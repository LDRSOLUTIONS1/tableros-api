<?php

namespace App\Support;

use App\Models\User;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

class IntranetJwt
{
    public const CODE_SESSION_EXPIRED = 'INTRANET_SESSION_EXPIRED';

    public const CODE_USER_MISMATCH = 'INTRANET_USER_MISMATCH';

    public function cookieName(): string
    {
        return (string) config('jwt.cookie_name', 'token');
    }

    public function requiresCookie(): bool
    {
        return (bool) config('jwt.require_intranet_cookie', false);
    }

    /**
     * Valida y decodifica el JWT recibido en la cookie de la intranet.
     *
     * @return array{valid: bool, payload: ?object, code: ?string, message: ?string}
     */
    public function validateRequest(Request $request): array
    {
        $jwt = $request->cookie($this->cookieName());

        if (! is_string($jwt) || trim($jwt) === '') {
            return [
                'valid' => false,
                'payload' => null,
                'code' => self::CODE_SESSION_EXPIRED,
                'message' => 'Tu sesión ha expirado. Inicia sesión nuevamente.',
            ];
        }

        $secret = config('jwt.secret_rh');

        if (! is_string($secret) || trim($secret) === '') {
            return [
                'valid' => false,
                'payload' => null,
                'code' => self::CODE_SESSION_EXPIRED,
                'message' => 'No fue posible validar tu sesión. Inicia sesión nuevamente.',
            ];
        }

        try {
            $decoded = JWT::decode($jwt, new Key($secret, 'HS256'));
        } catch (ExpiredException) {
            return [
                'valid' => false,
                'payload' => null,
                'code' => self::CODE_SESSION_EXPIRED,
                'message' => 'Tu sesión ha expirado. Inicia sesión nuevamente.',
            ];
        } catch (\Throwable) {
            return [
                'valid' => false,
                'payload' => null,
                'code' => self::CODE_SESSION_EXPIRED,
                'message' => 'Tu sesión no es válida o ha expirado. Inicia sesión nuevamente.',
            ];
        }

        return [
            'valid' => true,
            'payload' => $decoded,
            'code' => null,
            'message' => null,
        ];
    }

    /**
     * Comprueba que el JWT de la intranet corresponda al usuario autenticado en Sanctum.
     */
    public function matchesUser(object $decoded, User $user): bool
    {
        $tokenRhId = $decoded->id_colaborador ?? null;
        if ($user->external_rh_id !== null && $user->external_rh_id !== '' && $tokenRhId !== null && $tokenRhId !== '') {
            return (string) $user->external_rh_id === (string) $tokenRhId;
        }

        $tokenEmail = $decoded->correo ?? null;
        if ($user->email !== null && $user->email !== '' && is_string($tokenEmail) && trim($tokenEmail) !== '') {
            return mb_strtolower(trim($user->email)) === mb_strtolower(trim($tokenEmail));
        }

        $tokenCollaborator = $decoded->numero_colaborador ?? null;
        if ($user->collaborator_number !== null && $user->collaborator_number !== '' && $tokenCollaborator !== null && $tokenCollaborator !== '') {
            return (string) $user->collaborator_number === (string) $tokenCollaborator;
        }

        return false;
    }
}
