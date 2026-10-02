<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class McpToken
{
    /**
     * Autentica o servidor MCP por token estático (Bearer).
     *
     * Fase 1 do MCP: só há leitura de dados públicos, então um token único
     * compartilhado é suficiente. Ferramentas que toquem dados de membro
     * exigirão OAuth ou token por usuário.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Com o servidor desligado a rota não existe.
        if (! config('mcp.enabled')) {
            abort(404);
        }

        $esperado = (string) config('mcp.token');

        if ($esperado === '') {
            Log::warning('MCP habilitado sem MCP_TOKEN configurado: requisições recusadas.');

            abort(503);
        }

        $recebido = (string) $request->bearerToken();

        if ($recebido === '' || ! hash_equals($esperado, $recebido)) {
            return response()->json(
                ['message' => 'Não autenticado.'],
                401,
                ['WWW-Authenticate' => 'Bearer realm="mcp"'],
            );
        }

        return $next($request);
    }
}
