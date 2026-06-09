<?php

namespace App\Http\Middleware;

use App\Models\Sugestao;
use App\Models\PedidoOracao;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
            'novasSugestoes'      => $request->user() ? Sugestao::where('lida', false)->count() : null,
            'novosPedidos'        => $request->user() ? PedidoOracao::where('lido', false)->count() : null,
            'totalVisitantes'     => $request->user() ? Cache::remember('sidebar_total_visitantes', 300, fn () =>
                User::where('tipo', 'visitante')->where('is_superadmin', false)->count()
            ) : null,
            'novosVisitantesMes'  => $request->user() ? Cache::remember('sidebar_novos_visitantes_mes', 300, fn () =>
                User::where('tipo', 'visitante')->where('is_superadmin', false)
                    ->where('created_at', '>=', Carbon::today()->startOfMonth())->count()
            ) : null,
            'aniversariantesHoje' => $request->user() ? Cache::remember('sidebar_aniversariantes', 600, fn () => (function () {
                $hoje = Carbon::today();
                return User::whereNotNull('data_nascimento')
                    ->where('is_superadmin', false)
                    ->get()
                    ->filter(function ($u) use ($hoje) {
                        $nasc     = $u->data_nascimento;
                        $proxAniv = Carbon::create($hoje->year, $nasc->month, $nasc->day);
                        if ($proxAniv->lt($hoje)) $proxAniv->addYear();
                        return (int) $hoje->diffInDays($proxAniv) <= 30;
                    })
                    ->count();
            })()) : null,
            'hcaptchaSitekey'    => config('services.hcaptcha.sitekey'),
        ]);
    }
}
