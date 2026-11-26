<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\IntranetJwt;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyIntranetCookie
{
    public function __construct(private readonly IntranetJwt $intranetJwt) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->intranetJwt->requiresCookie()) {
            return $next($request);
        }

        $validation = $this->intranetJwt->validateRequest($request);

        if (! $validation['valid']) {
            return response()->json([
                'code' => $validation['code'],
                'message' => $validation['message'],
            ], 401);
        }

        $user = $request->user();

        if ($user instanceof User && ! $this->intranetJwt->matchesUser($validation['payload'], $user)) {
            return response()->json([
                'code' => IntranetJwt::CODE_USER_MISMATCH,
                'message' => 'La sesión de la intranet cambió de usuario.',
            ], 401);
        }

        return $next($request);
    }
}
