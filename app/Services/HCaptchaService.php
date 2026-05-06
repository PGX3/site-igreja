<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HCaptchaService
{
    public function verify(?string $token, string $ip): bool
    {
        if (empty($token)) {
            return false;
        }

        $secret = config('services.hcaptcha.secret');

        // Sitekey/secret de teste oficial do hCaptcha — sempre retorna sucesso
        if ($secret === '0x0000000000000000000000000000000000000000') {
            return true;
        }

        try {
            $response = Http::asForm()->post('https://hcaptcha.com/siteverify', [
                'secret'   => $secret,
                'response' => $token,
                'remoteip' => $ip,
            ]);

            return $response->json('success', false);
        } catch (\Exception $e) {
            Log::error('hCaptcha verification failed: ' . $e->getMessage());
            return false;
        }
    }
}
