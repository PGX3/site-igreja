<?php

namespace App\Notifications;

use App\Channels\FcmChannel;
use App\Models\Evento;
use App\Notifications\Messages\FcmMessage;
use Illuminate\Notifications\Notification;

class LembreteEvento extends Notification
{
    public function __construct(public readonly Evento $evento) {}

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
        $data    = $this->evento->data_evento?->format('d/m') ?? '';
        $horario = $this->evento->horario ? substr((string) $this->evento->horario, 0, 5) : '';
        $local   = $this->evento->local ? " · {$this->evento->local}" : '';

        return FcmMessage::make(
            "Amanhã: {$this->evento->nome}",
            trim("📅 {$data} {$horario}{$local}"),
        )->withData([
            'type'      => 'evento',
            'evento_id' => $this->evento->id,
        ]);
    }
}
