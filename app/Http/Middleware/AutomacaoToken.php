<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AutomacaoToken
{
    /**
     * Autentica automações internas por token estático (Bearer).
     * Sem AUTOMACAO_TOKEN configurado a rota não existe (404).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $esperado = (string) config('services.automacao.token');

        if ($esperado === '') {
            abort(404);
        }

        $recebido = (string) $request->bearerToken();

        if ($recebido === '' || ! hash_equals($esperado, $recebido)) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        return $next($request);
    }
}
