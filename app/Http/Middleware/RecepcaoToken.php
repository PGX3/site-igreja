<?php

namespace App\Http\Middleware;

use App\Models\RecepcaoDispositivo;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecepcaoToken
{
    /**
     * Autentica o aparelho da recepção pelo token (Bearer) gerado no painel.
     * O dispositivo fica em `$request->attributes->get('recepcao')`.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $dispositivo = RecepcaoDispositivo::porToken($request->bearerToken());

        if (! $dispositivo) {
            return response()->json(['message' => 'Aparelho não autorizado.'], 401);
        }

        $request->attributes->set('recepcao', $dispositivo);

        // Evita uma escrita por requisição: atualiza no máximo a cada minuto.
        if (! $dispositivo->ultimo_uso_em || $dispositivo->ultimo_uso_em->lt(now()->subMinute())) {
            $dispositivo->forceFill(['ultimo_uso_em' => now()])->saveQuietly();
        }

        return $next($request);
    }
}
