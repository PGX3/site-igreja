<?php

namespace App\Services;

use App\Models\Culto;
use Carbon\Carbon;

class CultoProximaDataResolver
{
    private const DIAS_PT = [
        'Domingo' => 0, 'Segunda' => 1, 'Terça' => 2,
        'Quarta'  => 3, 'Quinta'  => 4, 'Sexta'   => 5, 'Sábado' => 6,
    ];

    public function resolve(Culto $culto, ?Carbon $hoje = null): Carbon
    {
        $hoje    = $hoje ?? Carbon::today();
        $diaAlvo = self::DIAS_PT[$culto->dia_semana] ?? 0;
        $diff    = ($diaAlvo - (int) $hoje->format('w') + 7) % 7;

        return $hoje->copy()->addDays($diff ?: 7);
    }
}
