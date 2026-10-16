<?php

namespace App\Http\Controllers;

use App\Models\ExcelSource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
}
