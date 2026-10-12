<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Culto;
use App\Models\CultoExcecao;
use App\Services\AgendaSemana;
use App\Services\AniversariantesSemana;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoints consumidos por automações internas (agente que posta a agenda e os
 * aniversariantes da semana no grupo de WhatsApp). Protegido por token estático.
 */
class AutomacaoController extends Controller
{
    public function agendaSemana(Request $request): JsonResponse
    {
        return response()->json($this->agenda($this->referencia($request)));
    }

    public function aniversariantesSemana(Request $request): JsonResponse
    {
        $referencia = $this->referencia($request);

        $inicio = AniversariantesSemana::inicioSemana($referencia);
        $lista = AniversariantesSemana::listar($referencia);

        return response()->json([
            'semana_inicio' => $inicio->format('Y-m-d'),
            'semana_fim' => $inicio->copy()->addDays(6)->format('Y-m-d'),
            'total' => $lista->count(),
            'aniversariantes' => $lista->map(fn ($a) => [
                'nome' => AniversariantesSemana::primeiroEUltimoNome($a['user']->name),
                'data' => $a['data']->format('Y-m-d'),
                'dia_semana' => AniversariantesSemana::DIAS_SEMANA[$a['data']->dayOfWeek],
            ])->values(),
            'mensagem' => $lista->isEmpty() ? null : AniversariantesSemana::mensagem($lista),
        ]);
    }

    /** Cultos fixos, para o agente descobrir o culto_id. */
    public function cultos(): JsonResponse
    {
        return response()->json(Culto::orderBy('id')->get()->map(fn (Culto $c) => [
            'id' => $c->id,
            'nome' => $c->nome,
            'dia_semana' => $c->dia_semana,
            'horario' => AgendaSemana::formatarHora($c->hora_inicio ?: $c->horario),
            'ativo' => (bool) $c->ativo,
        ]));
    }

    /** Exceções que afetam a semana de `data` (padrão: semana atual). */
    public function excecoes(Request $request): JsonResponse
    {
        $inicio = AniversariantesSemana::inicioSemana($this->referencia($request));
        $fim = $inicio->copy()->addDays(6);

        $lista = CultoExcecao::with('culto')
            ->where(fn ($q) => $q
                ->where(fn ($w) => $w->whereDate('data', '>=', $inicio->toDateString())->whereDate('data', '<=', $fim->toDateString()))
                ->orWhere(fn ($w) => $w->whereDate('nova_data', '>=', $inicio->toDateString())->whereDate('nova_data', '<=', $fim->toDateString())))
            ->orderBy('data')
            ->get();

        return response()->json($lista->map(fn ($e) => $this->excecaoArray($e)));
    }

    /**
     * Cria/atualiza a exceção de um culto numa semana. `data` pode ser qualquer
     * dia da semana desejada: é normalizada para a ocorrência do culto nela.
     */
    public function salvarExcecao(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'culto_id' => ['required', 'integer', 'exists:cultos,id'],
            'data' => ['required', 'date_format:Y-m-d'],
            'nova_data' => ['nullable', 'date_format:Y-m-d'],
            'novo_horario' => ['nullable', 'string', 'max:20'],
            'cancelado' => ['nullable', 'boolean'],
            'observacao' => ['nullable', 'string', 'max:255'],
        ]);

        $culto = Culto::findOrFail($dados['culto_id']);
        $dia = AgendaSemana::diaDoCulto($culto);
        abort_if($dia === null, 422, 'Culto sem dia da semana reconhecível.');

        $inicio = AniversariantesSemana::inicioSemana(Carbon::createFromFormat('Y-m-d', $dados['data']));
        $ocorrencia = $inicio->copy()->addDays(($dia + 6) % 7);

        $excecao = CultoExcecao::where('culto_id', $culto->id)
            ->whereDate('data', $ocorrencia->toDateString())
            ->first() ?? new CultoExcecao(['culto_id' => $culto->id, 'data' => $ocorrencia->toDateString()]);

        $excecao->fill([
            'nova_data' => $dados['nova_data'] ?? null,
            'novo_horario' => $dados['novo_horario'] ?? null,
            'cancelado' => (bool) ($dados['cancelado'] ?? false),
            'observacao' => $dados['observacao'] ?? null,
        ])->save();

        return response()->json([
            'excecao' => $this->excecaoArray($excecao->load('culto')),
            'agenda' => $this->agenda($ocorrencia),
        ], $excecao->wasRecentlyCreated ? 201 : 200);
    }

    public function removerExcecao(CultoExcecao $excecao): JsonResponse
    {
        $excecao->delete();

        return response()->json(['message' => 'Exceção removida.']);
    }

    private function agenda(Carbon $referencia): array
    {
        $inicio = AniversariantesSemana::inicioSemana($referencia);
        $itens = AgendaSemana::listar($referencia);

        return [
            'semana_inicio' => $inicio->format('Y-m-d'),
            'semana_fim' => $inicio->copy()->addDays(6)->format('Y-m-d'),
            'total' => $itens->count(),
            'itens' => $itens->map(fn ($i) => [
                'tipo' => $i['tipo'],
                'culto_id' => $i['culto_id'],
                'nome' => $i['nome'],
                'data' => $i['data']->format('Y-m-d'),
                'horario' => $i['horario'],
                'local' => $i['local'],
                'aviso' => $i['aviso'],
                'cancelado' => $i['cancelado'],
            ])->values(),
            'mensagem' => $itens->isEmpty() ? null : AgendaSemana::mensagem($itens),
        ];
    }

    private function excecaoArray(CultoExcecao $e): array
    {
        return [
            'id' => $e->id,
            'culto_id' => $e->culto_id,
            'culto' => $e->culto?->nome,
            'data' => $e->data->format('Y-m-d'),
            'nova_data' => $e->nova_data?->format('Y-m-d'),
            'novo_horario' => $e->novo_horario,
            'cancelado' => $e->cancelado,
            'observacao' => $e->observacao,
        ];
    }

    private function referencia(Request $request): Carbon
    {
        $request->validate(['data' => ['nullable', 'date_format:Y-m-d']]);

        return $request->filled('data')
            ? Carbon::createFromFormat('Y-m-d', $request->input('data'))->startOfDay()
            : Carbon::today();
    }
}
