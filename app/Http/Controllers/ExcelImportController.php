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

        try {
            // Leer el archivo sin conocer la tabla de destino.
            $reader = new ExcelRowsReader();

            Excel::import(
                $reader,
                Storage::disk('local')->path($version->file_path)
            );

            $rows = $reader->rows
                ->filter(function ($row) {
                    return collect($row)->contains(
                        fn ($value) => $value !== null
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
        }
    }
}
