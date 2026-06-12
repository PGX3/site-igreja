<?php

namespace App\Jobs;

use App\Models\Culto;
use App\Models\Evento;
use App\Models\User;
use App\Notifications\LembreteCulto;
use App\Notifications\LembreteEvento;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class EnviarLembretesDia implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $hoje   = Carbon::today();
        $amanha = $hoje->copy()->addDay();
        $diaPt  = self::diaPt((int) $hoje->format('w'));

        $cultos = Culto::where('ativo', true)
            ->where('dia_semana', $diaPt)
            ->get();

        if ($cultos->isNotEmpty()) {
            $membrosComDevice = User::whereHas('deviceTokens')->get();
            foreach ($cultos as $culto) {
                Notification::send($membrosComDevice, new LembreteCulto($culto));
            }
        }

        $eventos = Evento::where('ativo', true)
            ->whereDate('data_evento', $amanha)
            ->get();

        if ($eventos->isNotEmpty()) {
            $membrosComDevice = User::whereHas('deviceTokens')->get();
            foreach ($eventos as $evento) {
                Notification::send($membrosComDevice, new LembreteEvento($evento));
            }
        }
    }

    private static function diaPt(int $w): string
    {
        return [
            0 => 'Domingo', 1 => 'Segunda', 2 => 'Terça',
            3 => 'Quarta',  4 => 'Quinta',  5 => 'Sexta', 6 => 'Sábado',
        ][$w];
    }
}
