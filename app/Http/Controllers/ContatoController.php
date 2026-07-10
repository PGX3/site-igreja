<?php

namespace App\Http\Controllers;

use App\Services\ContatoService;
use App\Services\RecaptchaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ContatoController extends Controller
{
    public function __construct(
        private RecaptchaService $captcha,
        private ContatoService $contatos,
    ) {}

    public function sugestao(Request $request)
    {
        // Rate limiting: máx 3 envios por IP por hora
        $key = 'sugestao:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withErrors([
                'mensagem' => "Muitas tentativas. Aguarde {$seconds} segundos.",
            ]);
        }

        // Verificar reCAPTCHA ANTES de validar os outros campos
        if (! $this->captcha->verify($request->input('g-recaptcha-response'), $request->ip(), 'sugestao')) {
            return back()->withErrors(['captcha' => 'Não foi possível confirmar que você não é um robô. Tente novamente.'])
                ->withInput();
        }

        RateLimiter::hit($key, 3600);

        $validated = $request->validate([
            'nome' => 'required|string|min:2|max:100',
            'email' => 'nullable|email|max:150',
            'mensagem' => 'required|string|min:10|max:2000',
        ], [
            'nome.required' => 'O nome é obrigatório.',
            'nome.min' => 'O nome deve ter ao menos 2 caracteres.',
            'mensagem.required' => 'A mensagem é obrigatória.',
            'mensagem.min' => 'A mensagem deve ter ao menos 10 caracteres.',
            'email.email' => 'Informe um e-mail válido.',
        ]);

        $this->contatos->criarSugestao($validated);

        return back()->with('sucesso_sugestao', 'Sua sugestão foi enviada com sucesso. Obrigado!');
    }

    public function pedidoOracao(Request $request)
    {
        // Rate limiting: máx 5 pedidos por IP por hora
        $key = 'pedido_oracao:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withErrors([
                'pedido' => "Muitas tentativas. Aguarde {$seconds} segundos.",
            ]);
        }

        // Verificar reCAPTCHA
        if (! $this->captcha->verify($request->input('g-recaptcha-response'), $request->ip(), 'pedido_oracao')) {
            return back()->withErrors(['captcha_oracao' => 'Não foi possível confirmar que você não é um robô. Tente novamente.'])
                ->withInput();
        }

        RateLimiter::hit($key, 3600);

        $validated = $request->validate([
            'nome' => 'required|string|min:2|max:100',
            'pedido' => 'required|string|min:10|max:2000',
            'anonimo' => 'boolean',
            'compartilhar' => 'boolean',
        ], [
            'nome.required' => 'O nome é obrigatório.',
            'nome.min' => 'O nome deve ter ao menos 2 caracteres.',
            'pedido.required' => 'O pedido é obrigatório.',
            'pedido.min' => 'Descreva melhor seu pedido (mínimo 10 caracteres).',
        ]);

        $this->contatos->criarPedidoOracao($validated);

        return back()->with('sucesso_oracao', 'Seu pedido foi recebido. Estaremos orando por você. 🙏');
    }
}
