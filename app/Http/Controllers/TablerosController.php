<?php

namespace App\Http\Controllers;

use App\Models\PowerBiDashboard;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TablerosController extends Controller

{
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        $user->load('role');

        if (in_array($user->role?->name, [
            'Super Administrador',
            'Administrador',
            'Consultor',
        ])) {
            $tableros = PowerBiDashboard::with([
                'category:id,nombre',
            ])
                ->select(
                    'id',
                    'category_id',
                    'nombre',
                    'descripcion',
                    'url',
                    'fuente',
                    'orden',
                    'estado',
                    'created_at',
                )
                ->activos()
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $tableros = $user->dashboards()
                ->with([
                    'category:id,nombre',
                ])
                ->select(
                    'power_bi_dashboards.id',
                    'power_bi_dashboards.category_id',
                    'power_bi_dashboards.nombre',
                    'power_bi_dashboards.descripcion',
                    'power_bi_dashboards.url',
                    'power_bi_dashboards.fuente',
                    'power_bi_dashboards.orden',
                    'power_bi_dashboards.estado',
                    'power_bi_dashboards.created_at',
                )
                ->activos()
                ->orderBy('power_bi_dashboards.id', 'desc')
                ->get();
        }

        return response()->json($tableros, 200);
    }

    public function store(Request $request)
    {
        $validated = $this->validateTableros($request);

        $tablero = PowerBiDashboard::create($validated);

        return response()->json([
            'message' => 'Tablero creado correctamente',
            'data'    => $tablero
        ], 201);
    }


    public function show($id)
    {
        $tablero = PowerBiDashboard::with([
            'category:id,nombre',
        ])->select(
            'id',
            'category_id',
            'nombre',
            'descripcion',
            'url',
            'fuente',
            'orden',
            'estado',
            'created_at',
        )
            ->where('id', $id)
            ->activos()
            ->firstOrFail();

        return response()->json($tablero, 200);
    }

    public function update(Request $request, $id)
    {
        $tablero = PowerBiDashboard::activos()
            ->findOrFail($id);

        $validated = $this->validateTableros($request, $id);

        $tablero->update($validated);

        return response()->json([
            'message' => 'Tablero actualizado correctamente',
            'data'    => $tablero
        ], 200);
    }

    public function validateTableros(Request $request, $id = null)
    {
        return $request->validate(
            [
                'category_id'   => 'required|exists:power_bi_categories,id',
                'nombre'        => 'required|string|max:255|unique:power_bi_dashboards,nombre,' . $id,
                'descripcion'   => 'nullable|string',
                'url'           => 'required|string',
                'fuente'        => 'nullable|string',
                'orden'         => 'nullable|integer',
                'estado'        => 'nullable|in:0,1,2',
            ],
            [
                'nombre.required' => 'El nombre es obligatorio',
                'nombre.max'      => 'El nombre no puede tener más de 255 caracteres',
                'nombre.unique'   => 'El nombre ya existe',
                'descripcion.max' => 'La descripción no puede tener más de 255 caracteres',
                'url.required'    => 'La URL es obligatoria',
                'fuente.max'      => 'La fuente no puede tener más de 255 caracteres',
                'estado.in'       => 'El estado debe ser 1 (Inactivo) o 2 (Activo).',
            ]
        );
    }
}
