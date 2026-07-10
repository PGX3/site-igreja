<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    /**
     * Verifica um token do reCAPTCHA v3 (score-based) via siteverify.
     * Falha fechado em produção: sem secret configurado, só libera em dev/testes.
     */
    public function verify(?string $token, string $ip, ?string $action = null): bool
    {
        $secret = config('services.recaptcha.secret');

        if (empty($secret)) {
            return app()->environment('local', 'testing');
        }

        if (empty($token)) {
            return false;
        }

        try {
            $data = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $ip,
            ])->json();

            if (! ($data['success'] ?? false)) {
                return false;
            }

            // A action retornada deve bater com a esperada (v3 assina a action no token).
            if ($action !== null && ($data['action'] ?? null) !== $action) {
                return false;
            }

            $threshold = (float) config('services.recaptcha.score_threshold', 0.5);

            return ($data['score'] ?? 0) >= $threshold;
        } catch (\Throwable $e) {
            Log::error('reCAPTCHA verification failed: '.$e->getMessage());

            return false;
        }
    }
}
