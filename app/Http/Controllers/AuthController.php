<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\User;
use App\Support\IntranetJwt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(private readonly IntranetJwt $intranetJwt) {}

    public function logincollaborator(Request $request, $collaborator_number)
    {
        try {
            $user = User::where('collaborator_number', $collaborator_number)->first();

            if (! $user) {
                return response()->json([
                    'error' => 'Empleado no encontrado.',
                ], 404);
            }

            $tokenResult = $user->createToken('auth_token');
            $token = $tokenResult->plainTextToken;

            AccessLog::create([
                'user_id' => $user->id,
                'token_id' => $tokenResult->accessToken->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'login_at' => now(),
            ]);

            return response()->json([
                'message' => 'Autenticación exitosa',
                'user' => $user,
                'token' => $token,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al autenticar empleado',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validation = $this->intranetJwt->validateRequest($request);

        if (! $validation['valid']) {
            return response()->json([
                'code' => $validation['code'],
                'message' => $validation['message'],
            ], 401);
        }

        $decoded = $validation['payload'];

        $user = User::where(
            'external_rh_id',
            $decoded->id_colaborador
        )->first();

        if (! $user) {
            $user = User::where(
                'email',
                $decoded->correo
            )->first();
        }

        if ($user) {
            $user->update([
                'external_rh_id' => $decoded->id_colaborador,
                'collaborator_number' => $decoded->numero_colaborador,
                'name' => $decoded->nombre,
                'email' => $decoded->correo,
                'brand' => $decoded->marca,
                'puesto' => $decoded->puesto,
                'area' => $decoded->area,
                'location_name' => $decoded->nombre_sede,
            ]);
        } else {
            $user = User::create([
                'external_rh_id' => $decoded->id_colaborador,
                'collaborator_number' => $decoded->numero_colaborador,
                'role_id' => 3,
                'name' => $decoded->nombre,
                'email' => $decoded->correo,
                'brand' => $decoded->marca,
                'location_name' => $decoded->nombre_sede,
                'puesto' => $decoded->puesto,
                'area' => $decoded->area,
                'password' => Hash::make(Str::random(40)),
            ]);
        }

        $tokenResult = $user->createToken('auth_token');
        $token = $tokenResult->plainTextToken;

        AccessLog::create([
            'user_id' => $user->id,
            'token_id' => $tokenResult->accessToken->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_at' => now(),
        ]);

        return response()->json([

            'token' => $token,
            'user' => $user,

        ]);
    }

    public function user(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'message' => 'Usuario autenticado',
            'user' => [
                'id' => $user->id,
                'collaborator_number' => $user->collaborator_number,
                'role_id' => $user->role_id,
                'name' => $user->name,
                'email' => $user->email,
                'brand' => $user->brand,
                'location_name' => $user->location_name,
                'puesto' => $user->puesto,
                'area' => $user->area,
                'estado' => $user->estado,
            ],
        ], 200);
    }

    public function checkSession(Request $request)
    {
        return response()->json([
            'authenticated' => true,
        ], 200);
    }
}
