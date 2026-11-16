<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepSeek
{
    public function configurado(): bool
    {
        return (bool) config('services.deepseek.api_key');
    }

    /**
     * Pede uma resposta em JSON. Devolve o array decodificado, ou null se a IA
     * não estiver configurada, falhar ou responder algo que não é JSON.
     */
    public function json(string $sistema, string $usuario): ?array
    {
        if (! $this->configurado()) {
            return null;
        }

        try {
            $res = Http::withToken(config('services.deepseek.api_key'))
                ->timeout(25)
                ->post(rtrim(config('services.deepseek.base_url'), '/').'/chat/completions', [
                    'model' => config('services.deepseek.model'),
                    'temperature' => 0,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => $sistema],
                        ['role' => 'user', 'content' => $usuario],
                    ],
                ]);

            if (! $res->successful()) {
                Log::warning("DeepSeek respondeu {$res->status()}");

                return null;
            }

            $json = json_decode((string) $res->json('choices.0.message.content'), true);

            return is_array($json) ? $json : null;
        } catch (\Throwable $e) {
            Log::warning("DeepSeek falhou: {$e->getMessage()}");

            return null;
        }
    }
}
