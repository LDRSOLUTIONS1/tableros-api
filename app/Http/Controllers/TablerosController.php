<?php

namespace App\Http\Controllers;

use App\Models\PowerBiDashboard;
use App\Models\User;
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

    public function indexName()
    {
        $roles = PowerBiDashboard::with([
            'category:id,nombre'
        ])
            ->select(
                'id',
                'category_id',
                'nombre',

            )
            ->activos()
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($roles, 200);
    }


    public function assign(Request $request)
    {
        $admin = $request->user();

        $admin->load('role');

        if (!in_array($admin->role?->name, [
            'Super Administrador',
            'Administrador'
        ])) {
            return response()->json([
                'message' => 'No tienes permisos para asignar tableros.'
            ], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'dashboard_ids' => 'present|array',
            'dashboard_ids.*' => [
                'integer',
                'distinct',
                'exists:power_bi_dashboards,id',
            ],
        ], [
            'user_id.required' => 'El ID del usuario es obligatorio.',
            'user_id.exists' => 'El usuario no existe.',
            'dashboard_ids.present' =>
            'Debes enviar la lista de tableros.',
            'dashboard_ids.array' =>
            'La lista de tableros no es válida.',
            'dashboard_ids.*.exists' =>
            'Alguno de los tableros no existe.',
            'dashboard_ids.*.distinct' =>
            'Hay tableros repetidos.',
        ]);

        $user = User::findOrFail($validated['user_id']);

        if ($user->role_id != 3) {
            return response()->json([
                'message' =>
                'Solo se pueden asignar tableros a usuarios con rol Limitado.'
            ], 422);
        }

        $user->dashboards()->sync(
            $validated['dashboard_ids']
        );

        return response()->json([
            'message' => 'Tableros asignados correctamente.',
        ], 200);
    }
}
