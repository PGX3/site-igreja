<?php

namespace App\Listeners;

use App\Events\MembroAdicionadoEscala;
use App\Notifications\EscalaConvite;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class EnviarNotificacaoEscalaConvite implements ShouldQueue
{
    public function handle(MembroAdicionadoEscala $event): void
    {
        try {
            $event->user->notify(new EscalaConvite($event->escala, $event->funcao));
        } catch (\Throwable $e) {
            Log::warning("Notificação de escala falhou [user {$event->user->id}]: {$e->getMessage()}");
        }
    }
}
