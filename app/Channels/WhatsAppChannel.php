<?php

namespace App\Channels;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
    public function send(mixed $notifiable, mixed $notification): void
    {
        if (! method_exists($notification, 'toWhatsApp')) {
            return;
        }

        $apikey = $notifiable->callmebot_apikey;
        $phone = $this->formatPhone($notifiable->telefone);

        if (! $apikey || ! $phone) {
            return;
        }

        $message = $notification->toWhatsApp($notifiable);
        if (! $message) {
            return;
        }

        try {
            Http::timeout(10)->get('https://api.callmebot.com/whatsapp.php', [
                'phone' => $phone,
                'text' => $message,
                'apikey' => $apikey,
            ]);
        } catch (\Throwable $e) {
            Log::warning("WhatsApp notification failed [{$notifiable->id}]: {$e->getMessage()}");
        }
    }

    private function formatPhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);

        // Número brasileiro: 10 ou 11 dígitos → adiciona DDI 55
        if (strlen($digits) === 10 || strlen($digits) === 11) {
            return '55'.$digits;
        }

        // Já tem DDI (12+ dígitos)
        if (strlen($digits) >= 12) {
            return $digits;
        }

        return null;
    }
}
