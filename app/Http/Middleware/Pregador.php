<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Pregador
{
    /** Libera os roteiros de pregação: pastor ou usuário marcado como pregador. */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->podePregar()) {
            abort(403, 'Acesso negado.');
        }

        return $next($request);
    }
}
