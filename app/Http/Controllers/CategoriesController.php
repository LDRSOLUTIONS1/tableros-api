<?php

namespace App\Http\Controllers;

use App\Models\PowerBiCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoriesController extends Controller
{
    public function index()
    {
        $categories = PowerBiCategory::select(
            'id',
            'nombre',
            'descripcion',
            'estado',
            'created_at',
            'updated_at',
        )
            ->activos()
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($categories, 200);
    }

    public function store(Request $request)
    {
        $validated = $this->validateCategories($request);

        $category = PowerBiCategory::create($validated);

        return response()->json([
            'message' => 'Categoría creada correctamente',
            'data'    => $category
        ], 201);
    }


    public function show($id)
    {
        $category = PowerBiCategory::select(
            'id',
            'nombre',
            'descripcion',
            'estado',
            'created_at',
            'updated_at',
        )
            ->where('id', $id)
            ->activos()
            ->firstOrFail();

        return response()->json($category, 200);
    }

    public function update(Request $request, $id)
    {
        $category = PowerBiCategory::activos()
            ->findOrFail($id);

        $validated = $this->validateCategories($request, $id);

        $category->update($validated);

        return response()->json([
            'message' => 'Categoría actualizada correctamente',
            'data'    => $category
        ], 200);
    }

    public function validateCategories(Request $request, $id = null)
    {
        return $request->validate(
            [
                'nombre' => 'required|string|max:255|unique:power_bi_categories,nombre,' . $id,
                'descripcion' => 'nullable|string|max:255',
                'estado' => 'nullable|in:0,1,2',
            ],
            [
                'nombre.required' => 'El nombre es obligatorio',
                'nombre.max'      => 'El nombre no puede tener más de 255 caracteres',
                'nombre.unique'   => 'El nombre ya existe',
                'descripcion.max' => 'La descripción no puede tener más de 255 caracteres',
                'estado.in'     => 'El estado debe ser 1 (Inactivo) o 2 (Activo).',
            ]
        );
    }
}
