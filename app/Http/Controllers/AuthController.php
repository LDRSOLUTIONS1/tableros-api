<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AccessLog;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function logincollaborator(Request $request, $collaborator_number)
    {
        try {
            $user = User::where('collaborator_number', $collaborator_number)->first();

            if (!$user) {
                return response()->json([
                    'error' => 'Empleado no encontrado.'
                ], 404);
            }

            $tokenResult = $user->createToken('auth_token');
            $token = $tokenResult->plainTextToken;

            AccessLog::create([
                'user_id'    => $user->id,
                'token_id'   => $tokenResult->accessToken->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'login_at'   => now(),
            ]);

            return response()->json([
                'message' => 'Autenticación exitosa',
                'user' => $user,
                'token' => $token
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al autenticar empleado',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $jwt = $request->cookie('token');

        if (!$jwt) {
            return response()->json([
                'message' => 'No existe token'
            ], 401);
        }

        try {
            $decoded = JWT::decode(
                $jwt,
                new Key(config('jwt.secret_rh'), 'HS256')
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Token inválido'
            ], 401);
        }

        $user = User::where(
            'external_rh_id',
            $decoded->id_colaborador
        )->first();

        if (!$user) {
            $user = User::where(
                'email',
                $decoded->correo
            )->first();
        }

        if ($user) {
            $user->update([
                'external_rh_id' => $decoded->id_colaborador,
                'collaborator_number' => $decoded->id_colaborador,
                'name' => $decoded->nombre,
                'email' => $decoded->correo,
                'brand' => $decoded->marca,
                'location_name' => $decoded->nombre_sede
            ]);
        } else {
            $user = User::create([
                'external_rh_id' => $decoded->id_colaborador,
                'collaborator_number' => $decoded->id_colaborador,
                'role_id' => 3,
                'name' => $decoded->nombre,
                'email' => $decoded->correo,
                'brand' => $decoded->marca,
                'location_name' => $decoded->nombre_sede,
                'password' => Hash::make(Str::random(40))
            ]);
        }

        $tokenResult = $user->createToken('reportes');
        $token = $tokenResult->plainTextToken;

        AccessLog::create([
            'user_id'    => $user->id,
            'token_id'   => $tokenResult->accessToken->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_at'   => now(),
        ]);

        return response()->json([

            'token' => $token,
            'user' => $user

        ]);
    }

    public function user(Request $request)
    {
        $user = $request->user()->load('segment');

        return response()->json([
            'message' => 'Usuario autenticado',
            'user' => [
                'id' => $user->id,
                'role_id' => $user->role_id,
                'collaborator_number' => $user->collaborator_number,
                'external_rh_id' => $user->external_rh_id,
                'name' => $user->name,
                'email' => $user->email,
                'brand' => $user->brand,
                'location_name' => $user->location_name,
                'estado' => $user->estado,
            ]
        ], 200);
    }
}
