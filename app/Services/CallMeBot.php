<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CallMeBot
{
    /**
     * Envia uma mensagem via CallMeBot (WhatsApp). Retorna true se a requisição foi feita.
     * Serve tanto para número pessoal quanto para grupo (o phone é o destino informado pelo CallMeBot).
     */
    public function enviar(?string $phone, ?string $apikey, string $mensagem): bool
    {
        if (! $apikey || trim($mensagem) === '') {
            return false;
        }

        // Número é opcional: alguns setups de grupo do CallMeBot usam só a apikey.
        $params = [
            'text' => $mensagem,
            'apikey' => $apikey,
        ];

        $phone = $this->formatPhone($phone);
        if ($phone) {
            $params['phone'] = $phone;
        }

        try {
            $res = Http::timeout(15)->get('https://api.callmebot.com/whatsapp.php', $params);

            return $res->successful();
        } catch (\Throwable $e) {
            Log::warning("CallMeBot falhou: {$e->getMessage()}");

            return false;
        }
    }

    private function formatPhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);

        if ($digits === '') {
            return null;
        }

        // Número brasileiro sem DDI: adiciona 55
        if (strlen($digits) === 10 || strlen($digits) === 11) {
            return '55'.$digits;
        }

        return $digits;
    }
}
