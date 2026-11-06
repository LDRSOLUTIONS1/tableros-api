<?php

namespace App\Http\Controllers;

use App\Models\ExcelSource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exports\ExcelSourceLayoutExport;
use Illuminate\Support\Str;
use App\Contracts\ExcelSourceImporter;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\PowerBiDashboard;

class ImportacionesController extends Controller
{
    public function index()
    {
        $importaciones = ExcelSource::select(
            'id',
            'codigo',
            'nombre',
            'descripcion',
            'current_version_id',
            'estado',
            'created_at',
            'updated_at',
        )
            ->activos()
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($importaciones, 200);
    }

    public function store(Request $request)
    {
        $validated = $this->validateImportaciones($request);

        $importacion = ExcelSource::create($validated);

        return response()->json([
            'message' => 'Fuente de Excel creada correctamente',
            'data'    => $importacion
        ], 201);
    }

    public function show($id)
    {
        $importacion = ExcelSource::with([
            'versions',
            'versions.importedBy:id,name,email',
            'versions.exportacionVehiculos',
            'currentVersion'

        ])
            ->select(
                'id',
                'codigo',
                'nombre',
                'descripcion',
                'current_version_id',
                'estado',
                'created_at',
                'updated_at',
            )
            ->where('id', $id)
            ->activos()
            ->firstOrFail();

        return response()->json($importacion, 200);
    }

    public function update(Request $request, $id)
    {
        $importacion = ExcelSource::activos()
            ->findOrFail($id);

        $validated = $this->validateImportaciones($request, $id);;

        $importacion->update($validated);

        return response()->json([
            'message' => 'Fuente de Excel actualizada correctamente',
            'data'    => $importacion
        ], 200);
    }

    private function validateImportaciones(Request $request, $id = null)
    {
        return $request->validate(
            [
                'codigo'        => 'nullable|string|max:255',
                'nombre'        => 'required|string|max:255|unique:excel_sources,nombre,' . $id,
                'descripcion'   => 'nullable|string|max:500',
                'estado'        => 'nullable|integer|in:1,2,3',
            ],
            [
                'codigo.max'      => 'El codigo no puede tener más de 255 caracteres',
                'nombre.required' => 'El nombre es obligatorio',
                'nombre.max'      => 'El nombre no puede tener más de 255 caracteres',
                'nombre.unique'   => 'El nombre ya existe',
                'descripcion.max' => 'La descripción no puede tener más de 500 caracteres',
                'estado.in'       => 'El estado debe ser 1 (Inactivo) o 2 (Activo).',
            ]
        );
    }

    public function downloadLayout(ExcelSource $excelSource)
    {
        if ((int) $excelSource->estado !== 2) {
            return response()->json([
                'message' => 'La fuente de Excel no está activa.',
            ], 422);
        }

        $handlerClass = config(
            'excel_importers.' . $excelSource->codigo
        );

        if (
            !$handlerClass ||
            !is_a(
                $handlerClass,
                ExcelSourceImporter::class,
                true
            )
        ) {
            return response()->json([
                'message' => 'Esta fuente no tiene un procesador configurado.',
            ], 422);
        }

        $handler = app($handlerClass);

        $headings = $handler->templateHeadings();

        if (empty($headings)) {
            return response()->json([
                'message' => 'Esta fuente no tiene un layout configurado.',
            ], 422);
        }

        $filename = Str::slug($excelSource->codigo) . '-layout.xlsx';

        return Excel::download(
            new ExcelSourceLayoutExport($headings),
            $filename
        );
    }

    public function porTablero(Request $request)
    {
        $user = $request->user();
        $user->loadMissing('role');

        $validated = $request->validate([
            'tablero_id' => 'nullable|integer|exists:power_bi_dashboards,id',
        ]);

        $query = PowerBiDashboard::query()
            ->select(
                'id',
                'nombre',
                'descripcion',
                'orden',
                'estado'
            )
            ->where('estado', 2)
            ->with([
                'pages' => function ($query) {
                    $query->where('estado', 2)
                        ->with([
                            'excelSources' => function ($query) {
                                $query->where('excel_sources.estado', 2)
                                    ->with('currentVersion');
                            }
                        ]);
                }
            ]);

        // Respetar los tableros asignados al usuario.
        if (!in_array($user->role?->name, [
            'Super Administrador',
            'Administrador',
            'Consultor',
        ])) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        // Filtrar por tablero cuando se solicite.
        if (!empty($validated['tablero_id'])) {
            $query->whereKey($validated['tablero_id']);
        }

        $tableros = $query
            ->orderBy('orden')
            ->get();

        return response()->json($tableros, 200);
    }
}
