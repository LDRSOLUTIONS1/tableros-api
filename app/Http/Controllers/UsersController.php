<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class UsersController extends Controller
{
    public function index()
    {
        $usuarios = User::with([
            'role:id,name',
        ])->select(
            'id',
            'collaborator_number',
            'role_id',
            'name',
            'email',
            'brand',
            'location_name',
            'puesto',
            'area',
            'estado',
            'created_at',
            'updated_at',
        )
            ->activos()
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($usuarios, 200);
    }

    public function indexLimited()
    {
        $usuarios = User::with([
            'role:id,name',
            'dashboards:id,nombre',
        ])
            ->select(
                'id',
                'collaborator_number',
                'role_id',
                'name',
                'email',
                'brand',
                'location_name',
                'puesto',
                'area',
                'estado',
                'created_at',
                'updated_at',
            )
            ->where('role_id', 3)
            ->activos()
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($usuarios, 200);
    }

    public function store(Request $request)
    {
        $validated = $this->validateUsers($request);

        $validated['password'] = Hash::make('password');

        $usuario = User::create($validated);

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'data'    => $usuario
        ], 201);
    }

    public function show($id)
    {
        $usuario = User::with([
            'role:id,name',
        ])->select(
            'id',
            'collaborator_number',
            'role_id',
            'name',
            'email',
            'brand',
            'location_name',
            'puesto',
            'area',
            'estado',
            'created_at',
            'updated_at',
        )
            ->where('id', $id)
            ->activos()
            ->firstOrFail();

        return response()->json($usuario, 200);
    }

    public function update(Request $request, $id)
    {
        $usuario = User::activos()
            ->findOrFail($id);

        $validated = $this->validateUsers($request, $id);

        $usuario->update($validated);

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'data'    => $usuario
        ], 200);
    }

    public function validateUsers(Request $request, $id = null)
    {
        return $request->validate(
            [
                'collaborator_number' => [
                    'required',
                    'numeric',
                    'unique:users,collaborator_number,' . $id,
                ],

                'role_id' => [
                    'required',
                    'integer',
                    'exists:roles,id',
                ],



                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'email' => [
                    'required',
                    'email',
                    'max:255',
                    'unique:users,email,' . $id,
                ],

                'estado' => [
                    'nullable',
                    'integer',
                    'in:0,1,2',
                ],
            ],
            [
                'collaborator_number.required' => 'El número de colaborador es obligatorio.',
                'collaborator_number.numeric'  => 'El número de colaborador debe ser numérico.',
                'collaborator_number.unique'   => 'El número de colaborador ya existe.',

                'role_id.required' => 'El rol es obligatorio.',
                'role_id.integer'  => 'El rol debe ser un número.',
                'role_id.exists'   => 'El rol seleccionado no existe.',

                'name.required' => 'El nombre es obligatorio.',
                'name.string'   => 'El nombre debe ser texto.',
                'name.max'      => 'El nombre no puede tener más de 255 caracteres.',

                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email'    => 'El correo electrónico debe ser válido.',
                'email.max'      => 'El correo electrónico no puede tener más de 255 caracteres.',
                'email.unique'   => 'El correo electrónico ya existe.',

                'estado.integer' => 'El estado debe ser un número.',
                'estado.in'      => 'El estado debe ser 0, 1 o 2.',
            ]
        );
    }
}
