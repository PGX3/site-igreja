<?php

namespace App\Http\Middleware;

use App\Models\Sugestao;
use App\Models\PedidoOracao;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user()
                    ? array_merge(
                        $request->user()->only('id', 'name', 'email'),
                        ['role' => $request->user()->role?->name]
                    )
                    : null,
            ],
            'flash' => [
                'success'          => $request->session()->get('success'),
                'error'            => $request->session()->get('error'),
                'sucesso_sugestao' => $request->session()->get('sucesso_sugestao'),
                'sucesso_oracao'   => $request->session()->get('sucesso_oracao'),
            ],
            // Badges no menu do admin (só carrega quando autenticado)
            'novasSugestoes' => $request->user() ? Sugestao::where('lida', false)->count() : null,
            'novosPedidos'       => $request->user() ? PedidoOracao::where('lido', false)->count() : null,
            'hcaptchaSitekey'    => config('services.hcaptcha.sitekey'),
        ]);
    }
}
