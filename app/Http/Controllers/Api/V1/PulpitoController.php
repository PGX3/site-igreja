<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Igreja;
use App\Models\Roteiro;
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

        $roteiros = Roteiro::with('autor:id,name')
            ->whereBetween('data', [
                $hoje->copy()->subDays(self::DIAS_ANTES)->toDateString(),
                $hoje->copy()->addDays(self::DIAS_DEPOIS)->toDateString(),
            ])
            ->orderBy('data')
            ->orderBy('titulo')
            ->get()
            ->map(fn (Roteiro $r) => $this->resumo($r))
            ->values();

        return response()->json(['data' => $roteiros]);
    }

    public function roteiro(Roteiro $roteiro): JsonResponse
    {
        return response()->json(['data' => array_merge($this->resumo($roteiro), [
            'conteudo' => $roteiro->conteudo ?? '',
            'tamanho_fonte' => $roteiro->tamanho_fonte,
        ])]);
    }

    private function resumo(Roteiro $r): array
    {
        return [
            'id' => $r->id,
            'titulo' => $r->titulo,
            'data' => $r->data->format('Y-m-d'),
            'versiculo' => $r->versiculo,
            'pregador' => $r->autor?->name,
            'atualizado_em' => $r->updated_at?->toIso8601String(),
        ];
    }
}
