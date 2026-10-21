<?php

namespace App\Providers;

use App\Models\User;
use Dedoc\Scramble\Scramble;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureRateLimiters();
        $this->configureApiDocs();
    }

    /**
     * Documentação da API do app em /docs/api (Scramble), para qualquer usuário logado no painel.
     * As rotas da automação do WhatsApp ficam de fora: usam token fixo e não são para o app.
     */
    protected function configureApiDocs(): void
    {
        Gate::define('viewApiDocs', fn (User $user) => true);

        Scramble::configure()->routes(fn (Route $route) => Str::startsWith($route->uri, 'api/v1/')
            && ! Str::startsWith($route->uri, 'api/v1/automacao'));
    }

    protected function configureRateLimiters(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email');

            return [
                Limit::perMinute(5)->by($request->ip().'|'.$email),
                Limit::perMinute(20)->by($request->ip()),
            ];
        });

        RateLimiter::for('contato', function (Request $request) {
            return Limit::perHour(3)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('contato-oracao', function (Request $request) {
            return Limit::perHour(5)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('devices', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('cadastro', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });

        // Agentes de IA fazem várias chamadas por resposta (tools/list + N tools/call).
        RateLimiter::for('mcp', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });
    }
}
