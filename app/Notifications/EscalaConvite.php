<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Models\Escala;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EscalaConvite extends Notification
{
    public function __construct(
        public readonly Escala  $escala,
        public readonly ?string $funcao = null,
    ) {}

    public function via(mixed $notifiable): array
    {
        $channels = ['mail'];

        if ($notifiable->callmebot_apikey && $notifiable->telefone) {
            $channels[] = WhatsAppChannel::class;
        }

        return $channels;
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $escala = $this->escala;
        $data   = $escala->data?->format('d/m/Y') ?? '';
        $inicio = substr((string) $escala->hora_inicio, 0, 5);
        $fim    = substr((string) $escala->hora_fim,    0, 5);
        $grupo  = $escala->grupo?->nome ?? '';

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
            ->action('Ver Minhas Escalas', config('app.url') . '/admin/minhas-escalas')
            ->line('Acesse o sistema para confirmar ou recusar sua participação.')
            ->salutation('Que Deus abençoe! — ' . config('app.name'));
    }

    public function toWhatsApp(mixed $notifiable): string
    {
        $escala = $this->escala;
        $data   = $escala->data?->format('d/m/Y') ?? '';
        $inicio = substr((string) $escala->hora_inicio, 0, 5);
        $fim    = substr((string) $escala->hora_fim,    0, 5);
        $grupo  = $escala->grupo?->nome ?? '';

        $msg  = "Olá {$notifiable->name}! 👋\n\n";
        $msg .= "Você foi escalado(a) para:\n\n";
        $msg .= "📋 *{$escala->titulo}*\n";
        $msg .= "📅 {$data}   ⏰ {$inicio} – {$fim}\n";
        $msg .= "👥 Grupo: {$grupo}\n";

        if ($this->funcao) {
            $msg .= "🎯 Função: {$this->funcao}\n";
        }

        $msg .= "\nAcesse o sistema para confirmar ou recusar sua participação.";

        return $msg;
    }
}
