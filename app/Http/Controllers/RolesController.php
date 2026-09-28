<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RolesController extends Controller
{
    public function index()
    {
        $roles = Role::select(
            'id',
            'name',
            'estado',
            'created_at',
        )
            ->activos()
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($roles, 200);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRoles($request);

        $role = Role::create($validated);

        return response()->json([
            'message' => 'Rol creado correctamente',
            'data'    => $role
        ], 201);
    }


    public function show($id)
    {
        $role = Role::select(
            'id',
            'name',
            'estado',
            'created_at',
        )
            ->where('id', $id)
            ->activos()
            ->firstOrFail();

        return response()->json($role, 200);
    }

    public function update(Request $request, $id)
    {
        $role = Role::activos()
            ->findOrFail($id);

        $validated = $this->validateRoles($request, $id);

        $role->update($validated);

        return response()->json([
            'message' => 'Rol actualizado correctamente',
            'data'    => $role
        ], 200);
    }

    public function validateRoles(Request $request, $id = null)
    {
        return $request->validate(
            [
                'name' => 'required|string|max:255|unique:roles,name,' . $id,
                'estado' => 'nullable|in:0,1,2',
            ],
            [
                'name.required' => 'El nombre es obligatorio',
                'name.max'      => 'El nombre no puede tener más de 255 caracteres',
                'name.unique'   => 'El nombre ya existe',
                'estado.in'     => 'El estado debe ser 1 (Inactivo) o 2 (Activo).',
            ]
        );
    }
}
