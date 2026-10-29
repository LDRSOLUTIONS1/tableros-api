<?php

namespace App\Http\Controllers;

use App\Contracts\ExcelSourceImporter;
use App\Imports\ExcelRowsReader;
use App\Models\ExcelImportVersion;
use App\Models\ExcelSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Model;
use App\Support\XlsxTrailingRowsCleaner;
use Throwable;

class ExcelImportController extends Controller
{
    public function store(Request $request, ExcelSource $excelSource)
    {
        if ((int) $excelSource->estado !== 2) {
            return response()->json([
                'message' => 'La fuente de Excel no está activa.',
            ], 422);
        }

        // Resolver el procesador usando el código de la fuente.
        $handlerClass = config(
            'excel_importers.' . $excelSource->codigo
        );

        if (
            !$handlerClass
            || !is_a(
                $handlerClass,
                ExcelSourceImporter::class,
                true
            )
        ) {
            return response()->json([
                'message' => 'Esta fuente no tiene un procesador configurado.',
            ], 422);
        }

        $request->validate([
            'archivo' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:20480',
            ],
        ], [
            'archivo.required' => 'Debes seleccionar un archivo.',
            'archivo.file' => 'El archivo no se recibió correctamente.',
            'archivo.mimes' => 'El archivo debe ser XLSX, XLS o CSV.',
            'archivo.max' => 'El archivo no debe superar los 20 MB.',
        ]);

        $file = $request->file('archivo');
        $fileHash = hash_file('sha256', $file->getRealPath());

        // No aceptar exactamente el mismo archivo para la fuente.
        if (
            $excelSource->versions()
            ->where('file_hash', $fileHash)
            ->exists()
        ) {
            return response()->json([
                'message' => 'Este archivo ya fue registrado para esta fuente.',
            ], 409);
        }

        $filePath = $file->store(
            'excel-imports/' . $excelSource->codigo,
            'local'
        );

        if (!$filePath) {
            return response()->json([
                'message' => 'No fue posible almacenar el archivo.',
            ], 500);
        }

        try {
            // Crear una versión antes del procesamiento para conservar
            // también los intentos que terminen en error.
            $version = DB::transaction(function () use (
                $excelSource,
                $file,
                $fileHash,
                $filePath
            ) {
                $source = ExcelSource::whereKey($excelSource->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Revalidar duplicados bajo el bloqueo de la fuente.
                if (
                    $source->versions()
                    ->where('file_hash', $fileHash)
                    ->exists()
                ) {
                    throw new \RuntimeException(
                        'DUPLICATE_EXCEL_FILE'
                    );
                }

                $nextVersion = (int) $source->versions()
                    ->max('version_number') + 1;

                return $source->versions()->create([
                    'version_number' => $nextVersion,
                    'original_filename' => mb_substr(
                        $file->getClientOriginalName(),
                        0,
                        255
                    ),
                    'file_path' => $filePath,
                    'file_hash' => $fileHash,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'status' => 'processing',
                    'total_rows' => 0,
                    'imported_by' => Auth::id(),
                    'error_message' => null,
                    'imported_at' => null,
                ]);
            });
        } catch (Throwable $e) {
            Storage::disk('local')->delete($filePath);

            if (
                $excelSource->versions()
                ->where('file_hash', $fileHash)
                ->exists()
            ) {
                return response()->json([
                    'message' => 'Este archivo ya fue registrado para esta fuente.',
                ], 409);
            }

            report($e);

            return response()->json([
                'message' => 'No fue posible registrar la versión del archivo.',
            ], 500);
        }

        $tempImportPath = null;

        try {
            $originalPath = Storage::disk('local')->path(
                $version->file_path
            );

            $importPath = $originalPath;

            if (
                strtolower($file->getClientOriginalExtension()) === 'xlsx'
            ) {
                $tempImportPath = app(
                    XlsxTrailingRowsCleaner::class
                )->cleanCopy($originalPath);

                $importPath = $tempImportPath;
            }

            // Leer el archivo sin conocer la tabla de destino.
            $reader = new ExcelRowsReader();

            Excel::import($reader, $importPath);

            $rows = $reader->rows
                ->filter(function ($row) {
                    return collect($row)->contains(
                        fn($value) => $value !== null
                            && trim((string) $value) !== ''
                    );
                })
                ->values();

            if ($rows->isEmpty()) {
                throw ValidationException::withMessages([
                    'archivo' => ['El archivo no contiene registros.'],
                ]);
            }

            // Verificar los encabezados definidos por el procesador.
            $handler = app($handlerClass);
            $firstRow = collect($rows->first())->toArray();
            $headers = array_keys($firstRow);

            $missing = array_diff(
                $handler->requiredHeadings(),
                $headers
            );

            if (!empty($missing)) {
                throw ValidationException::withMessages([
                    'archivo' => [
                        'Faltan las columnas requeridas: '
                            . implode(', ', $missing),
                    ],
                ]);
            }

            /*
                * Los datos, el estado completed y la versión actual se
                * actualizan en una misma transacción. Si ocurre un error,
                * se revierten los registros y se conserva el historial.
                */
            $totalRows = DB::transaction(function () use (
                $handler,
                $rows,
                $version,
                $excelSource
            ) {
                $total = $handler->import($rows, $version->id);

                $version->update([
                    'status' => 'completed',
                    'total_rows' => $total,
                    'imported_at' => now(),
                    'error_message' => null,
                ]);

                $source = ExcelSource::whereKey($excelSource->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $currentVersionNumber = 0;

                if ($source->current_version_id) {
                    $currentVersionNumber = (int)
                    ExcelImportVersion::whereKey(
                        $source->current_version_id
                    )->value('version_number');
                }

                // Evita que una importación anterior finalizada tarde
                // reemplace una versión más reciente.
                if (
                    $version->version_number
                    > $currentVersionNumber
                ) {
                    $source->update([
                        'current_version_id' => $version->id,
                    ]);
                }

                return $total;
            });

            return response()->json([
                'message' => 'Archivo Excel importado correctamente.',
                'data' => [
                    'source_id' => $excelSource->id,
                    'version_id' => $version->id,
                    'version_number' => $version->version_number,
                    'status' => 'completed',
                    'total_rows' => $totalRows,
                    'imported_at' => $version->fresh()->imported_at,
                ],
            ], 201);
        } catch (ValidationException $e) {
            $message = implode(
                ' ',
                collect($e->errors())->flatten()->all()
            );

            $version->update([
                'status' => 'failed',
                'error_message' => mb_substr($message, 0, 60000),
            ]);

            return response()->json([
                'message' => 'El archivo no cumple con la estructura o los datos requeridos.',
                'errors' => $e->errors(),
                'data' => [
                    'version_id' => $version->id,
                    'version_number' => $version->version_number,
                    'status' => 'failed',
                ],
            ], 422);
        } catch (Throwable $e) {
            report($e);

            $version->update([
                'status' => 'failed',
                'error_message' => mb_substr(
                    $e->getMessage(),
                    0,
                    60000
                ),
            ]);

            return response()->json([
                'message' => 'Ocurrió un error al procesar el archivo. La versión actual se conserva.',
                'data' => [
                    'version_id' => $version->id,
                    'version_number' => $version->version_number,
                    'status' => 'failed',
                ],
            ], 500);
        } finally {
            if ($tempImportPath && is_file($tempImportPath)) {
                @unlink($tempImportPath);
            }
        }
    }

    public function datosActuales(Request $request, ExcelSource $excelSource)
    {
        $config = config(
            'excel_datasets.' . $excelSource->codigo
        );

        if (
            !$config ||
            !isset($config['model']) ||
            !class_exists($config['model']) ||
            !is_subclass_of($config['model'], Model::class)
        ) {
            return response()->json([
                'message' => 'La fuente no tiene un conjunto de datos configurado.',
            ], 422);
        }

        $modelClass = $config['model'];

        $version = $excelSource
            ->currentVersion()
            ->with('importedBy:id,name,email')
            ->first();

        $columns = array_values(array_diff(
            (new $modelClass())->getFillable(),
            [
                'excel_import_version_id',
                'registro_hash',
            ]
        ));

        if (!empty($config['columns'])) {
            $columns = $config['columns'];
        }

        $pagination = [
            'current_page' => 1,
            'per_page' => min(
                max($request->integer('per_page', 25), 1),
                100
            ),
            'total' => 0,
            'last_page' => 1,
        ];

        $rows = [];

        if ($version && $version->status === 'completed') {
            $perPage = $pagination['per_page'];

            $registros = $modelClass::query()
                ->select(array_values(array_unique([
                    'id',
                    ...$columns,
                ])))
                ->orderBy('id')
                ->paginate($perPage);

            $rows = $registros->items();

            $pagination = [
                'current_page' => $registros->currentPage(),
                'per_page' => $registros->perPage(),
                'total' => $registros->total(),
                'last_page' => $registros->lastPage(),
            ];
        }

        return response()->json([
            'source' => [
                'id' => $excelSource->id,
                'codigo' => $excelSource->codigo,
                'nombre' => $excelSource->nombre,
                'descripcion' => $excelSource->descripcion,
                'estado' => $excelSource->estado,
            ],

            'dataset' => [
                'label' => $config['label'] ?? $excelSource->nombre,
                'columns' => $columns,
            ],

            'version' => $version ? [
                'id' => $version->id,
                'version_number' => $version->version_number,
                'original_filename' => $version->original_filename,
                'status' => $version->status,
                'total_rows' => $version->total_rows,
                'imported_at' => $version->imported_at,
                'imported_by' => $version->importedBy ? [
                    'id' => $version->importedBy->id,
                    'name' => $version->importedBy->name,
                    'email' => $version->importedBy->email,
                ] : null,
            ] : null,

            'rows' => $rows,

            'pagination' => $pagination,

            'download_url' => $version
                ? $this->generarUrlDescarga($excelSource, $version)
                : null,
        ]);
    }

    public function versiones(ExcelSource $excelSource)
    {
        $versions = $excelSource->versions()
            ->select([
                'id',
                'excel_source_id',
                'version_number',
                'original_filename',
                'status',
                'total_rows',
                'imported_by',
                'error_message',
                'imported_at',
                'created_at',
            ])
            ->with('importedBy:id,name,email')
            ->orderByDesc('version_number')
            ->get();

        return response()->json([
            'source' => [
                'id' => $excelSource->id,
                'codigo' => $excelSource->codigo,
                'nombre' => $excelSource->nombre,
                'current_version_id' => $excelSource->current_version_id,
            ],

            'versions' => $versions->map(function ($version) use ($excelSource) {
                return [
                    'id' => $version->id,
                    'version_number' => $version->version_number,
                    'original_filename' => $version->original_filename,
                    'status' => $version->status,
                    'total_rows' => $version->total_rows,
                    'error_message' => $version->error_message,
                    'imported_at' => $version->imported_at,
                    'created_at' => $version->created_at,

                    'is_current' => (
                        $version->id === $excelSource->current_version_id
                    ),

                    'imported_by' => $version->importedBy ? [
                        'id' => $version->importedBy->id,
                        'name' => $version->importedBy->name,
                        'email' => $version->importedBy->email,
                    ] : null,

                    'download_url' => $this->generarUrlDescarga(
                        $excelSource,
                        $version
                    ),
                ];
            })->values(),
        ]);
    }

    public function descargar(ExcelSource $excelSource, ExcelImportVersion $version)
    {
        abort_unless(
            (int) $version->excel_source_id === (int) $excelSource->id,
            404
        );

        $disk = Storage::disk('local');
        $path = $version->file_path;

        if (!$path || !$disk->exists($path)) {
            return response()->json([
                'message' => 'El archivo de esta versión ya no existe o no está disponible.',
            ], 404);
        }

        $filename = $version->original_filename ?: basename($path);

        return $disk->download($path, $filename);
    }

    private function generarUrlDescarga(ExcelSource $excelSource, ExcelImportVersion $version): string
    {
        $path = route(
            'importaciones.versiones.descargar',
            [
                'excelSource' => $excelSource->id,
                'version' => $version->id,
            ],
            false
        );

        return rtrim(config('app.url'), '/')
            . '/'
            . ltrim($path, '/');
    }
}
