<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Culto;
use App\Models\Escala;
use App\Models\Evento;
use App\Models\Grupo;
use App\Services\InterpretadorRestricoes;
use App\Services\SugestorEscala;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanejadorController extends Controller
{
    private const SEPARADOR_FUNCOES = ', ';

    /** Quantos dias para trás contam como carga recente na sugestão e no resumo. */
    private const DIAS_HISTORICO = 90;

    private array $diasPt = [
        'Domingo' => 0, 'Segunda' => 1, 'Terça' => 2,
        'Quarta' => 3, 'Quinta' => 4, 'Sexta' => 5, 'Sábado' => 6,
    ];

    public function index(Request $request)
    {
        $user = auth()->user();

        // Todos os grupos aparecem para escolha; a edição é travada por liderança.
        $grupos = Grupo::orderBy('nome')->get(['id', 'nome']);

        $grupoId = (int) $request->query('grupo_id') ?: ($grupos->first()->id ?? null);
        $mes = $request->query('mes') ?: Carbon::today()->format('Y-m');

        $inicio = Carbon::createFromFormat('Y-m-d', $mes.'-01')->startOfMonth();
        $fim = $inicio->copy()->endOfMonth();

        $grupo = $grupoId
            ? Grupo::with(['funcoes:id,grupo_id,nome', 'funcoes.membros:id', 'membros:id,name', 'cultosSemEscala:id'])->find($grupoId)
            : null;

        $funcoes = $grupo ? $grupo->funcoes->pluck('nome')->values() : collect();
        $membros = $grupo ? $grupo->membros->map(fn ($m) => ['id' => $m->id, 'nome' => $m->name])->values() : collect();

        $rows = collect();

        if ($grupo) {
            // Escalas existentes do grupo no mês
            $escalas = Escala::with('escalaMembros')
                ->where('grupo_id', $grupoId)
                ->whereBetween('data', [$inicio->toDateString(), $fim->toDateString()])
                ->get();

            foreach ($escalas as $e) {
                $rows[$this->key($e->data, $e->culto_id, $e->evento_id)] = $this->rowFromEscala($e, $funcoes->all());
            }

            // Candidatos: cultos (recorrentes por dia da semana), menos os que o grupo não escala.
            // Escalas já criadas aparecem sempre (vieram do bloco acima).
            $cultosSemEscala = $grupo->cultosSemEscala->pluck('id')->all();
            foreach (Culto::where('ativo', true)->whereNotIn('id', $cultosSemEscala)->get() as $culto) {
                $alvo = $this->diasPt[$culto->dia_semana] ?? null;
                if ($alvo === null) {
                    continue;
                }
                $d = $inicio->copy();
                while ($d->lte($fim)) {
                    if ((int) $d->dayOfWeek === $alvo) {
                        $k = $this->key($d, $culto->id, null);
                        if (! isset($rows[$k])) {
                            $rows[$k] = $this->rowVazia($d, $culto->id, null, $culto->nome, $funcoes);
                        }
                    }
                    $d->addDay();
                }
            }

            // Candidatos: eventos do mês, se o grupo escala eventos
            $eventos = $grupo->escala_eventos
                ? Evento::where('ativo', true)
                    ->whereBetween('data_evento', [$inicio->toDateString(), $fim->toDateString()])
                    ->get()
                : collect();
            foreach ($eventos as $ev) {
                $k = $this->key($ev->data_evento, null, $ev->id);
                if (! isset($rows[$k])) {
                    $rows[$k] = $this->rowVazia($ev->data_evento, null, $ev->id, $ev->nome, $funcoes);
                }
            }
        }

        $rows = $rows->sortBy(fn ($r) => $r['data'].($r['vinculo'] ?? ''))->values();

        $ocupados = $grupo ? $this->ocupados($grupoId, $inicio, $fim) : [];

        return Inertia::render('Admin/Planejador', [
            'editavel' => $grupoId ? $user->canManageGrupo($grupoId) : false,
            'ocupados' => $ocupados,
            'grupos' => $grupos,
            'grupo_id' => $grupoId,
            'mes' => $inicio->format('Y-m'),
            'label' => ucfirst($inicio->translatedFormat('F Y')),
            'prev' => $inicio->copy()->subMonth()->format('Y-m'),
            'next' => $inicio->copy()->addMonth()->format('Y-m'),
            'funcoes' => $funcoes,
            'membros' => $membros,
            'aptos' => $grupo ? (object) $this->aptos($grupo) : (object) [],
            'historico' => $grupo ? (object) $this->resumoHistorico($grupo, $inicio) : (object) [],
            'rows' => $rows,
        ]);
    }

    /**
     * Sugere quem escalar nas células vazias da grade (não salva nada).
     * Equilibra pela carga de cada pessoa e respeita função, outros grupos e "Não pode".
     */
    public function sugerir(Request $request, SugestorEscala $sugestor, InterpretadorRestricoes $interprete): JsonResponse
    {
        $data = $request->validate([
            'grupo_id' => 'required|exists:grupos,id',
            'mes' => 'required|date_format:Y-m',
            'rows' => 'required|array',
            'rows.*.data' => 'required|date_format:Y-m-d',
            'rows.*.vinculo' => 'nullable|string',
            'rows.*.restricoes' => 'nullable|string',
            'rows.*.cells' => 'nullable|array',
        ]);

        $grupoId = (int) $data['grupo_id'];
        if (! $request->user()->canManageGrupo($grupoId)) {
            abort(403);
        }

        $grupo = Grupo::with(['funcoes:id,grupo_id,nome', 'funcoes.membros:id', 'membros:id,name'])->find($grupoId);
        $inicio = Carbon::createFromFormat('Y-m-d', $data['mes'].'-01')->startOfMonth();
        $fim = $inicio->copy()->endOfMonth();

        $linhas = array_values($data['rows']);
        $membros = $grupo->membros->pluck('name', 'id')->all();

        $lido = $interprete->interpretar(
            array_map(fn ($r) => [
                'data' => $r['data'],
                'vinculo' => $r['vinculo'] ?? null,
                'texto' => (string) ($r['restricoes'] ?? ''),
            ], $linhas),
            $membros,
        );

        $resultado = $sugestor->sugerir(
            linhas: array_map(fn ($r) => ['data' => $r['data'], 'cells' => $r['cells'] ?? []], $linhas),
            funcoes: $grupo->funcoes->pluck('nome')->all(),
            membros: array_keys($membros),
            aptos: $this->aptos($grupo),
            historico: $this->historico(array_keys($membros), $inicio),
            ocupados: $this->ocupados($grupoId, $inicio, $fim),
            naoPodem: $lido['nao_podem'],
        );

        return response()->json([
            'sugestoes' => $resultado['sugestoes'],
            'sem_candidato' => $resultado['sem_candidato'],
            'nao_podem' => collect($lido['nao_podem'])
                ->map(fn ($ids, $linha) => ['linha' => $linha, 'ids' => $ids])
                ->values(),
            'ia' => $lido['ia'],
        ]);
    }

    /** Pessoas já escaladas em OUTROS grupos no mês: data => [user_id => nome do grupo]. */
    private function ocupados(int $grupoId, Carbon $inicio, Carbon $fim): array
    {
        $ocupados = [];

        $outras = Escala::with(['grupo:id,nome', 'escalaMembros:id,escala_id,user_id'])
            ->where('grupo_id', '!=', $grupoId)
            ->whereBetween('data', [$inicio->toDateString(), $fim->toDateString()])
            ->get();

        foreach ($outras as $e) {
            $d = $e->data->format('Y-m-d');
            foreach ($e->escalaMembros as $em) {
                $ocupados[$d][$em->user_id] = $e->grupo?->nome ?? 'outro grupo';
            }
        }

        return $ocupados;
    }

    /** Função => ids de quem faz (só membros atuais). Função sem ninguém marcado não aparece. */
    private function aptos(Grupo $grupo): array
    {
        $membros = $grupo->membros->pluck('id');

        return $grupo->funcoes
            ->mapWithKeys(fn ($f) => [$f->nome => $f->membros->pluck('id')->intersect($membros)->values()->all()])
            ->filter()
            ->all();
    }

    /** user_id => datas (Y-m-d) em que serviu nos últimos dias antes do mês, em qualquer grupo. */
    private function historico(array $membrosIds, Carbon $inicio): array
    {
        $historico = [];

        $linhas = Escala::query()
            ->join('escala_membros', 'escala_membros.escala_id', '=', 'escalas.id')
            ->whereIn('escala_membros.user_id', $membrosIds)
            ->where('escalas.data', '<', $inicio->toDateString())
            ->where('escalas.data', '>=', $inicio->copy()->subDays(self::DIAS_HISTORICO)->toDateString())
            ->orderBy('escalas.data')
            ->get(['escala_membros.user_id', 'escalas.data']);

        foreach ($linhas as $l) {
            $historico[$l->user_id][] = Carbon::parse($l->data)->format('Y-m-d');
        }

        return $historico;
    }

    /** Para o resumo da tela: user_id => ['recentes' => n, 'ultima' => 'Y-m-d']. */
    private function resumoHistorico(Grupo $grupo, Carbon $inicio): array
    {
        return collect($this->historico($grupo->membros->pluck('id')->all(), $inicio))
            ->map(fn ($datas) => ['recentes' => count($datas), 'ultima' => end($datas)])
            ->all();
    }

    public function salvar(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'grupo_id' => 'required|exists:grupos,id',
            'rows' => 'array',
        ]);

        $grupoId = (int) $data['grupo_id'];
        if (! $user->canManageGrupo($grupoId)) {
            abort(403);
        }

        // Ordem das colunas, para gravar "Camera, Instagram" sempre na mesma ordem
        $ordemFuncoes = Grupo::with('funcoes:id,grupo_id,nome')->find($grupoId)
            ->funcoes->pluck('nome')->flip();

        foreach ($request->rows ?? [] as $row) {
            $assignments = collect($row['cells'] ?? [])->filter(fn ($v) => ! empty($v)); // funcao => user_id
            $restricoes = trim((string) ($row['restricoes'] ?? '')) ?: null;
            $temConteudo = $assignments->isNotEmpty() || $restricoes;

            $escala = ! empty($row['escala_id']) ? Escala::find($row['escala_id']) : null;
            if ($escala && $escala->grupo_id !== $grupoId) {
                continue;
            }

            if (! $escala) {
                if (! $temConteudo) {
                    continue; // não cria linha vazia
                }

                $culto = ! empty($row['culto_id'])
                    ? Culto::find($row['culto_id'])
                    : null;

                $escala = Escala::create([
                    'titulo' => $row['vinculo'] ?: ('Escala '.Carbon::parse($row['data'])->format('d/m/Y')),
                    'data' => $row['data'],
                    'hora_inicio' => $this->horaInicioDoCulto($culto) ?? '00:00',
                    'hora_fim' => $this->horaFimDoCulto($culto) ?? '00:00',
                    'grupo_id' => $grupoId,
                    'culto_id' => $row['culto_id'] ?? null,
                    'evento_id' => $row['evento_id'] ?? null,
                    'status' => 'pendente',
                    'restricoes' => $restricoes,
                    'created_by' => $user->id,
                ]);
            } else {
                $escala->update(['restricoes' => $restricoes, 'updated_by' => $user->id]);
            }

            // Sincroniza membros: uma linha por pessoa na escala (unique escala_id+user_id).
            // Quem está em mais de uma função fica com elas juntas: "Camera, Instagram".
            $porPessoa = []; // user_id => [funcao, ...]
            foreach ($assignments as $funcao => $userId) {
                $porPessoa[(int) $userId][] = $funcao;
            }

            $desejados = collect($porPessoa)
                ->map(fn ($fs) => collect($fs)
                    ->sortBy(fn ($f) => $ordemFuncoes[$f] ?? PHP_INT_MAX)
                    ->implode(self::SEPARADOR_FUNCOES))
                ->all(); // user_id => "Funcao A, Funcao B"

            $escala->escalaMembros()->whereNotIn('user_id', array_keys($desejados) ?: [0])->delete();

            foreach ($desejados as $uid => $funcao) {
                $escala->escalaMembros()->updateOrCreate(
                    ['user_id' => $uid],
                    ['funcao' => $funcao],
                );
            }
        }

        return back()->with('success', 'Planejamento salvo!');
    }

    private function key($data, $cultoId, $eventoId): string
    {
        $d = $data instanceof Carbon ? $data->format('Y-m-d') : (string) $data;

        return $d.'|'.($cultoId ?? '').'|'.($eventoId ?? '');
    }

    private function rowFromEscala(Escala $e, array $funcoes): array
    {
        $cells = [];
        foreach ($e->escalaMembros as $em) {
            if ($em->funcao) {
                foreach ($this->separarFuncoes($em->funcao, $funcoes) as $funcao) {
                    $cells[$funcao] = $em->user_id;
                }
            }
        }

        return [
            'escala_id' => $e->id,
            'data' => $e->data->format('Y-m-d'),
            'dia' => $e->data->format('d/m'),
            'semana' => $e->data->translatedFormat('D'),
            'culto_id' => $e->culto_id,
            'evento_id' => $e->evento_id,
            'vinculo' => $e->titulo,
            'restricoes' => $e->restricoes ?? '',
            'cells' => (object) $cells,
        ];
    }

    /**
     * "Camera, Instagram" vira ['Camera', 'Instagram'] quando as duas são funções do grupo.
     * Se não bater com as funções, devolve o texto inteiro (como sempre foi).
     */
    private function separarFuncoes(string $funcao, array $funcoes): array
    {
        if (in_array($funcao, $funcoes, true)) {
            return [$funcao];
        }

        $partes = array_map('trim', explode(trim(self::SEPARADOR_FUNCOES), $funcao));

        return array_diff($partes, $funcoes) === [] ? $partes : [$funcao];
    }

    private function rowVazia(Carbon $data, ?int $cultoId, ?int $eventoId, ?string $vinculo, $funcoes): array
    {
        return [
            'escala_id' => null,
            'data' => $data->format('Y-m-d'),
            'dia' => $data->format('d/m'),
            'semana' => $data->translatedFormat('D'),
            'culto_id' => $cultoId,
            'evento_id' => $eventoId,
            'vinculo' => $vinculo,
            'restricoes' => '',
            'cells' => (object) [],
        ];
    }

    private function horaInicioDoCulto(?Culto $culto): ?string
    {
        if (! $culto) {
            return null;
        }

        return $culto->hora_inicio ?: $this->normalizarHorario($culto->horario);
    }

    private function horaFimDoCulto(?Culto $culto): ?string
    {
        if (! $culto) {
            return null;
        }

        // Cultos antigos só têm um horário. Até serem atualizados, evita 00:00.
        return $culto->hora_fim ?: $this->horaInicioDoCulto($culto);
    }

    private function normalizarHorario(?string $horario): ?string
    {
        if (! $horario || ! preg_match('/^(\d{1,2})[:h](\d{2})$/i', trim($horario), $partes)) {
            return null;
        }

        $hora = (int) $partes[1];
        $minuto = (int) $partes[2];

        if ($hora > 23 || $minuto > 59) {
            return null;
        }

        return sprintf('%02d:%02d', $hora, $minuto);
    }
}
