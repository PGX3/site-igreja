<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Culto;
use App\Models\Escala;
use App\Models\Evento;
use App\Models\Grupo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanejadorController extends Controller
{
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
            ? Grupo::with(['funcoes:id,grupo_id,nome', 'membros:id,name'])->find($grupoId)
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
                $rows[$this->key($e->data, $e->culto_id, $e->evento_id)] = $this->rowFromEscala($e);
            }

            // Candidatos: cultos (recorrentes por dia da semana)
            foreach (Culto::where('ativo', true)->get() as $culto) {
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

            // Candidatos: eventos do mês
            foreach (Evento::where('ativo', true)
                ->whereBetween('data_evento', [$inicio->toDateString(), $fim->toDateString()])
                ->get() as $ev) {
                $k = $this->key($ev->data_evento, null, $ev->id);
                if (! isset($rows[$k])) {
                    $rows[$k] = $this->rowVazia($ev->data_evento, null, $ev->id, $ev->nome, $funcoes);
                }
            }
        }

        $rows = $rows->sortBy(fn ($r) => $r['data'].($r['vinculo'] ?? ''))->values();

        // Pessoas já escaladas em OUTROS grupos no mês (para evitar choque de agenda)
        $ocupados = [];
        if ($grupo) {
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
        }

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
            'rows' => $rows,
        ]);
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
                $escala = Escala::create([
                    'titulo' => $row['vinculo'] ?: ('Escala '.Carbon::parse($row['data'])->format('d/m/Y')),
                    'data' => $row['data'],
                    'hora_inicio' => $row['hora_inicio'] ?? '00:00',
                    'hora_fim' => $row['hora_fim'] ?? '00:00',
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

            // Sincroniza membros: um usuário por escala (respeita unique escala_id+user_id)
            $desejados = []; // user_id => funcao
            foreach ($assignments as $funcao => $userId) {
                $uid = (int) $userId;
                if (! isset($desejados[$uid])) {
                    $desejados[$uid] = $funcao;
                }
            }

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

    private function rowFromEscala(Escala $e): array
    {
        $cells = [];
        foreach ($e->escalaMembros as $em) {
            if ($em->funcao) {
                $cells[$em->funcao] = $em->user_id;
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
}
