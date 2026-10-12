<?php

namespace App\Services;

use App\Models\Culto;
use App\Models\CultoExcecao;
use App\Models\Evento;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Agenda de uma semana (segunda a domingo): cultos fixos ativos (com as
 * exceções da semana aplicadas) + eventos, e a mensagem pronta para o WhatsApp.
 */
class AgendaSemana
{
    public const DIAS_CULTO = [
        'domingo' => 0, 'segunda' => 1, 'terça' => 2, 'terca' => 2,
        'quarta' => 3, 'quinta' => 4, 'sexta' => 5, 'sábado' => 6, 'sabado' => 6,
    ];

    private const DIAS_CURTO = [
        0 => 'DOMINGO', 1 => 'SEGUNDA', 2 => 'TERÇA', 3 => 'QUARTA',
        4 => 'QUINTA', 5 => 'SEXTA', 6 => 'SÁBADO',
    ];

    /**
     * @return Collection<int, array{tipo: string, culto_id: ?int, nome: string, data: Carbon, horario: ?string, local: ?string, aviso: ?string, cancelado: bool}>
     */
    public static function listar(Carbon $referencia): Collection
    {
        $inicio = AniversariantesSemana::inicioSemana($referencia);
        $fim = $inicio->copy()->addDays(6);

        $excecoes = CultoExcecao::where(fn ($q) => $q
            ->where(fn ($w) => $w->whereDate('data', '>=', $inicio->toDateString())->whereDate('data', '<=', $fim->toDateString()))
            ->orWhere(fn ($w) => $w->whereDate('nova_data', '>=', $inicio->toDateString())->whereDate('nova_data', '<=', $fim->toDateString())))
            ->get();

        $itens = collect();

        foreach (Culto::where('ativo', true)->get() as $culto) {
            $dia = self::diaDoCulto($culto);
            if ($dia === null) {
                continue;
            }

            $original = $inicio->copy()->addDays(($dia + 6) % 7);
            $excecao = $excecoes->first(fn ($e) => $e->culto_id === $culto->id && $e->data->isSameDay($original));

            // Ocorrência de outra semana remarcada para dentro desta
            foreach ($excecoes as $e) {
                if ($e->culto_id === $culto->id && $e !== $excecao && ! $e->cancelado
                    && $e->nova_data && $e->nova_data->between($inicio, $fim)) {
                    $itens->push(self::itemCulto($culto, $e->data->copy(), $e));
                }
            }

            if ($excecao && ! $excecao->cancelado && $excecao->nova_data && ! $excecao->nova_data->between($inicio, $fim)) {
                continue; // remarcado para outra semana
            }

            $itens->push(self::itemCulto($culto, $original, $excecao));
        }

        $eventos = Evento::where('ativo', true)
            ->whereDate('data_evento', '>=', $inicio)
            ->whereDate('data_evento', '<=', $fim)
            ->get()
            ->map(fn (Evento $e) => [
                'tipo' => 'evento',
                'culto_id' => null,
                'nome' => $e->nome,
                'data' => $e->data_evento->copy()->startOfDay(),
                'horario' => self::formatarHora($e->horario),
                'local' => $e->local ?: null,
                'aviso' => null,
                'cancelado' => false,
            ]);

        return $itens->concat($eventos)
            ->sortBy(fn ($i) => sprintf('%010d-%04d', $i['data']->timestamp, self::minutos($i['horario'])))
            ->values();
    }

    private static function itemCulto(Culto $culto, Carbon $original, ?CultoExcecao $excecao): array
    {
        $data = $original->copy()->startOfDay();
        $horario = self::formatarHora($culto->hora_inicio ?: $culto->horario);
        $aviso = null;
        $cancelado = false;

        if ($excecao) {
            $cancelado = $excecao->cancelado;

            if ($cancelado) {
                $aviso = 'Excepcionalmente não teremos este culto nesta semana.';
            } else {
                if ($excecao->nova_data) {
                    $data = $excecao->nova_data->copy()->startOfDay();
                }
                if ($excecao->novo_horario) {
                    $horario = self::formatarHora($excecao->novo_horario);
                }

                if (! $data->isSameDay($original)) {
                    $aviso = 'Excepcionalmente nesta semana será '
                        .(in_array($data->dayOfWeek, [0, 6], true) ? 'no ' : 'na ')
                        .AniversariantesSemana::DIAS_SEMANA[$data->dayOfWeek]
                        .($excecao->novo_horario ? ', às '.$horario : '').'.';
                } elseif ($excecao->novo_horario) {
                    $aviso = 'Excepcionalmente nesta semana será às '.$horario.'.';
                }
            }

            $aviso = $excecao->observacao ?: $aviso;
        }

        return [
            'tipo' => 'culto',
            'culto_id' => $culto->id,
            'nome' => $culto->nome,
            'data' => $data,
            'horario' => $horario,
            'local' => null,
            'aviso' => $aviso,
            'cancelado' => $cancelado,
        ];
    }

    public static function mensagem(Collection $itens): string
    {
        $linhas = ['📅 AGENDA DA SEMANA — IGREJA EM CHARQUEADAS'];

        foreach ($itens as $i) {
            $cabecalho = self::emoji($i).' '.self::DIAS_CURTO[$i['data']->dayOfWeek];
            if ($i['tipo'] === 'evento') {
                $cabecalho .= ' ('.$i['data']->format('d/m').')';
            }
            if ($i['horario'] && ! $i['cancelado']) {
                $cabecalho .= ' | '.$i['horario'];
            }

            $linhas[] = '';
            $linhas[] = $cabecalho;
            $linhas[] = $i['cancelado'] ? '~'.$i['nome'].'~' : $i['nome'];
            if ($i['local']) {
                $linhas[] = '📍 '.$i['local'];
            }
            if ($i['aviso']) {
                $linhas[] = '⚠️ '.$i['aviso'];
            }
        }

        $linhas[] = '';
        $linhas[] = '❤️ Esperamos vocês!';

        return implode("\n", $linhas);
    }

    private static function emoji(array $i): string
    {
        if ($i['tipo'] === 'evento') {
            return '📌';
        }

        $nome = mb_strtolower($i['nome']);

        return match (true) {
            str_contains($nome, 'oração') || str_contains($nome, 'oracao') => '🙏',
            str_contains($nome, 'jove') => '🔥',
            str_contains($nome, 'criança') || str_contains($nome, 'kids') => '🧒',
            default => '⛪',
        };
    }

    public static function diaDoCulto(Culto $culto): ?int
    {
        $chave = mb_strtolower(trim(explode('-', (string) $culto->dia_semana)[0]));

        return self::DIAS_CULTO[$chave] ?? null;
    }

    /** "19:30:00" → "19h30", "19:00" → "19h"; textos livres ("15h") ficam como estão. */
    public static function formatarHora(?string $hora): ?string
    {
        $hora = trim((string) $hora);

        if ($hora === '') {
            return null;
        }

        if (preg_match('/^(\d{1,2}):(\d{2})(:\d{2})?$/', $hora, $m)) {
            return (int) $m[1].'h'.($m[2] === '00' ? '' : $m[2]);
        }

        return $hora;
    }

    private static function minutos(?string $hora): int
    {
        if ($hora && preg_match('/(\d{1,2})\s*[h:]\s*(\d{2})?/i', $hora, $m)) {
            return (int) $m[1] * 60 + (int) ($m[2] ?? 0);
        }

        return 9999;
    }
}
