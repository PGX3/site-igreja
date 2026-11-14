<?php

namespace App\Http\Middleware;

use App\Models\PulpitoDispositivo;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PulpitoToken
{
    /** Autentica o iPad do púlpito pelo token (Bearer) gerado no painel. */
    public function handle(Request $request, Closure $next): Response
    {
        $dispositivo = PulpitoDispositivo::porToken($request->bearerToken());

        if (! $dispositivo) {
            return response()->json(['message' => 'Aparelho não autorizado.'], 401);
        }

        $request->attributes->set('pulpito', $dispositivo);

        if (! $dispositivo->ultimo_uso_em || $dispositivo->ultimo_uso_em->lt(now()->subMinute())) {
            $dispositivo->forceFill(['ultimo_uso_em' => now()])->saveQuietly();
        }

        return $next($request);
    }
}
