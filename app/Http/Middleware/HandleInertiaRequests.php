<?php

namespace App\Http\Middleware;

use App\Models\PedidoOracao;
use App\Models\Sugestao;
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
        $user = $request->user();

        // Badges de pessoas (visitantes/aniversários): pastor e líder.
        $verPessoas = $user && ($user->isPastor() || $user->isLider());
        // Badges de comunidade (sugestões/pedidos de oração): só pastor.
        $verComunidade = $user && $user->isPastor();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user
                    ? array_merge(
                        $user->only('id', 'name', 'email'),
                        ['role' => $user->role?->name]
                    )
                    : null,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'sucesso_sugestao' => $request->session()->get('sucesso_sugestao'),
                'sucesso_oracao' => $request->session()->get('sucesso_oracao'),
            ],
            // Badges no menu do admin (só para os papéis que enxergam cada seção)
            'novasSugestoes' => $verComunidade ? Sugestao::where('lida', false)->count() : null,
            'novosPedidos' => $verComunidade ? PedidoOracao::where('status', PedidoOracao::STATUS_NOVO)->count() : null,
            'totalVisitantes' => $verPessoas ? Cache::remember('sidebar_total_visitantes', 300, fn () => User::where('tipo', 'visitante')->where('is_superadmin', false)->count()
            ) : null,
            'novosVisitantesMes' => $verPessoas ? Cache::remember('sidebar_novos_visitantes_mes', 300, fn () => User::where('tipo', 'visitante')->where('is_superadmin', false)
                ->where('created_at', '>=', Carbon::today()->startOfMonth())->count()
            ) : null,
            'aniversariantesHoje' => $verPessoas ? Cache::remember('sidebar_aniversariantes', 600, fn () => (function () {
                $hoje = Carbon::today();

                return User::whereNotNull('data_nascimento')
                    ->where('is_superadmin', false)
                    ->get()
                    ->filter(function ($u) use ($hoje) {
                        $nasc = $u->data_nascimento;
                        $proxAniv = Carbon::create($hoje->year, $nasc->month, $nasc->day);
                        if ($proxAniv->lt($hoje)) {
                            $proxAniv->addYear();
                        }

                        return (int) $hoje->diffInDays($proxAniv) <= 30;
                    })
                    ->count();
            })()) : null,
            'hcaptchaSitekey' => config('services.hcaptcha.sitekey'),
        ]);
    }
}
