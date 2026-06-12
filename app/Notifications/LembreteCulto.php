<?php

namespace App\Notifications;

use App\Channels\FcmChannel;
use App\Models\Culto;
use App\Notifications\Messages\FcmMessage;
use Illuminate\Notifications\Notification;

class LembreteCulto extends Notification
{
    public function __construct(public readonly Culto $culto) {}

    public function via(mixed $notifiable): array
    {
        if (method_exists($notifiable, 'routeNotificationForFcm')
            && ! empty($notifiable->routeNotificationForFcm())) {
            return [FcmChannel::class];
        }

        return [];
    }

    public function toFcm(mixed $notifiable): FcmMessage
    {
        $horario = $this->culto->horario ? substr((string) $this->culto->horario, 0, 5) : '';

        return FcmMessage::make(
            "Hoje tem {$this->culto->nome}",
            $horario ? "Te esperamos às {$horario} na igreja." : 'Te esperamos hoje na igreja.',
        )->withData([
            'type'     => 'culto',
            'culto_id' => $this->culto->id,
        ]);
    }
}
