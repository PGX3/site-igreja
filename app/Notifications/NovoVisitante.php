<?php

namespace App\Notifications;

use App\Channels\FcmChannel;
use App\Models\User;
use App\Notifications\Messages\FcmMessage;
use Illuminate\Notifications\Notification;

/** Push para os pastores quando alguém se cadastra no iPad da recepção. */
class NovoVisitante extends Notification
{
    public function __construct(
        public readonly User $visitante,
        public readonly string $origem = 'Recepção',
    ) {}

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
        $v = $this->visitante;
        $corpo = $v->como_conheceu ? "Conheceu por: {$v->como_conheceu}" : 'Cadastro feito na recepção';

        return FcmMessage::make("Novo visitante: {$v->name}", "{$corpo} · {$this->origem}")
            ->withData([
                'type' => 'visitante',
                'visitante_id' => $v->id,
            ]);
    }
}
