<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Igreja;
use App\Models\Pregacao;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API do iPad do púlpito (app Pregação). Autenticado pelo token do aparelho
 * (middleware `pulpito.token`). Só leitura: lista e baixa os roteiros.
 */
class PulpitoController extends Controller
{
    /** Janela de roteiros que aparecem no iPad: da semana passada até o próximo mês. */
    public const DIAS_ANTES = 7;

    public const DIAS_DEPOIS = 30;

    public function config(Request $request): JsonResponse
    {
        return response()->json([
            'dispositivo' => $request->attributes->get('pulpito')->nome,
            'igreja' => Igreja::query()->value('nome') ?: config('app.name'),
        ]);
    }

    public function roteiros(): JsonResponse
    {
        $hoje = now()->startOfDay();

        $roteiros = Pregacao::query()
            ->whereNotNull('roteiro')->where('roteiro', '!=', '')
            ->whereBetween('data_pregacao', [
                $hoje->copy()->subDays(self::DIAS_ANTES)->toDateString(),
                $hoje->copy()->addDays(self::DIAS_DEPOIS)->toDateString(),
            ])
            ->orderBy('data_pregacao')
            ->orderBy('titulo')
            ->get()
            ->map(fn (Pregacao $p) => $this->resumo($p))
            ->values();

        return response()->json(['data' => $roteiros]);
    }

    /** O id é o da pregação (o roteiro faz parte dela). */
    public function roteiro(Pregacao $pregacao): JsonResponse
    {
        return response()->json(['data' => array_merge($this->resumo($pregacao), [
            'conteudo' => $pregacao->roteiro ?? '',
            'tamanho_fonte' => $pregacao->roteiro_fonte,
        ])]);
    }

    private function resumo(Pregacao $p): array
    {
        return [
            'id' => $p->id,
            'titulo' => $p->titulo,
            'data' => $p->data_pregacao->format('Y-m-d'),
            'versiculo' => $p->versiculo,
            'pregador' => $p->pregador,
            'atualizado_em' => $p->updated_at?->toIso8601String(),
        ];
    }
}
