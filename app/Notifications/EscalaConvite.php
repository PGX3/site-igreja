<?php

namespace App\Notifications;

use App\Channels\FcmChannel;
use App\Models\Escala;
use App\Notifications\Messages\FcmMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EscalaConvite extends Notification
{
    public function __construct(
        public readonly Escala $escala,
        public readonly ?string $funcao = null,
    ) {}

    public function via(mixed $notifiable): array
    {
        $channels = ['mail'];

        if (method_exists($notifiable, 'routeNotificationForFcm')
            && ! empty($notifiable->routeNotificationForFcm())) {
            $channels[] = FcmChannel::class;
        }

        return $channels;
    }

    public function toFcm(mixed $notifiable): FcmMessage
    {
        $escala = $this->escala;
        $data   = $escala->data?->format('d/m/Y') ?? '';
        $inicio = substr((string) $escala->hora_inicio, 0, 5);

        $body = "📅 {$data} às {$inicio}";
        if ($this->funcao) {
            $body .= " · 🎯 {$this->funcao}";
        }

        return FcmMessage::make("Nova escala: {$escala->titulo}", $body)
            ->withData([
                'type'      => 'escala',
                'escala_id' => $escala->id,
            ]);
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $escala = $this->escala;
        $data = $escala->data?->format('d/m/Y') ?? '';
        $inicio = substr((string) $escala->hora_inicio, 0, 5);
        $fim = substr((string) $escala->hora_fim, 0, 5);
        $grupo = $escala->grupo?->nome ?? '';

        $mail = (new MailMessage)
            ->subject("Você foi escalado: {$escala->titulo}")
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Você foi adicionado(a) à escala **{$escala->titulo}**.")
            ->line("📅 **Data:** {$data}")
            ->line("⏰ **Horário:** {$inicio} – {$fim}")
            ->line("👥 **Grupo:** {$grupo}");

        if ($this->funcao) {
            $mail->line("🎯 **Sua função:** {$this->funcao}");
        }

        return $mail
            ->action('Ver Minhas Escalas', config('app.url').'/admin/minhas-escalas')
            ->line('Acesse o sistema para confirmar ou recusar sua participação.')
            ->salutation('Que Deus abençoe! — '.config('app.name'));
    }
}
