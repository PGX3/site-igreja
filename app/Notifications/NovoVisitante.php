<?php

namespace App\Notifications;

use App\Channels\FcmChannel;
use App\Models\User;
use App\Notifications\Messages\FcmMessage;
use Illuminate\Notifications\Notification;

/**
 * Push para os pastores quando alguém se cadastra no iPad da recepção.
 * Também avisa quando a pessoa já estava cadastrada (visitante que voltou ou membro).
 */
class NovoVisitante extends Notification
{
    public function __construct(
        public readonly User $visitante,
        public readonly string $origem = 'Recepção',
        public readonly bool $retorno = false,
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

        if ($this->retorno) {
            $titulo = $v->tipo === 'membro' ? "Cadastro na recepção: {$v->name}" : "Visitante voltou: {$v->name}";
            $corpo = $v->tipo === 'membro'
                ? 'Já é membro, nada foi alterado'
                : 'Já estava cadastrado'.($v->primeira_visita ? ' (1ª visita em '.$v->primeira_visita->format('d/m/Y').')' : '');
        } else {
            $titulo = "Novo visitante: {$v->name}";
            $corpo = $v->como_conheceu ? "Conheceu por: {$v->como_conheceu}" : 'Cadastro feito na recepção';
        }

        return FcmMessage::make($titulo, "{$corpo} · {$this->origem}")
            ->withData([
                'type' => 'visitante',
                'visitante_id' => $v->id,
                'retorno' => $this->retorno ? '1' : '0',
            ]);
    }
}
