<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Aniversariantes de uma semana (segunda a domingo) e a mensagem pronta
 * para o grupo de WhatsApp. Usado pelo painel e pela automação semanal.
 */
class AniversariantesSemana
{
    public const DIAS_SEMANA = [
        0 => 'domingo', 1 => 'segunda-feira', 2 => 'terça-feira', 3 => 'quarta-feira',
        4 => 'quinta-feira', 5 => 'sexta-feira', 6 => 'sábado',
    ];

    public static function inicioSemana(Carbon $referencia): Carbon
    {
        return $referencia->copy()->startOfDay()->startOfWeek(Carbon::MONDAY);
    }

    /**
     * @return Collection<int, array{user: User, data: Carbon}>
     */
    public static function listar(Carbon $referencia): Collection
    {
        $inicio = self::inicioSemana($referencia);
        $dias = collect(range(0, 6))->map(fn ($i) => $inicio->copy()->addDays($i));

        return User::whereNotNull('data_nascimento')
            ->where('is_superadmin', false)
            ->get()
            ->map(function (User $u) use ($dias) {
                $nasc = $u->data_nascimento;
                $data = $dias->first(fn ($d) => $d->month === $nasc->month && $d->day === $nasc->day);

                return $data ? ['user' => $u, 'data' => $data] : null;
            })
            ->filter()
            ->sortBy(fn ($a) => $a['data']->timestamp)
            ->values();
    }

    public static function mensagem(Collection $aniversariantes): string
    {
        $linhas = $aniversariantes->map(function ($a) {
            $dia = ucfirst(self::DIAS_SEMANA[$a['data']->dayOfWeek]);

            return '🎂 '.self::primeiroEUltimoNome($a['user']->name)." - {$dia} (dia {$a['data']->day})";
        });

        return implode("\n", [
            '🎉 *Aniversariantes da semana* 🎉',
            '',
            'Vamos celebrar e orar por nossos irmãos que fazem aniversário nesta semana:',
            '',
            ...$linhas,
            '',
            'Que Deus abençoe grandemente a vida de cada um! 🙏🥳',
        ]);
    }

    public static function primeiroEUltimoNome(?string $nome): string
    {
        $partes = preg_split('/\s+/', trim((string) $nome), -1, PREG_SPLIT_NO_EMPTY);

        if (count($partes) <= 1) {
            return $partes[0] ?? '';
        }

        return $partes[0].' '.$partes[count($partes) - 1];
    }
}
