<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PedidoOracao;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Acompanhamento dos pedidos de oração pelo pastor (mesma regra do painel).
 * O envio de pedidos pelo membro continua em POST /contato/pedido-oracao.
 */
class PedidoOracaoController extends Controller
{
    /** `?status=` filtra por um dos status (novo, em_oracao, inativo, concluido). */
    public function index(Request $request): JsonResponse
    {
        $request->validate(['status' => ['nullable', Rule::in(PedidoOracao::STATUSES)]]);

        $pedidos = PedidoOracao::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (PedidoOracao $p) => self::pedido($p));

        return response()->json(['data' => $pedidos]);
    }

    public function atualizarStatus(Request $request, PedidoOracao $pedido): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(PedidoOracao::STATUSES)],
        ]);

        $pedido->update(['status' => $data['status']]);

        return response()->json(['data' => self::pedido($pedido)]);
    }

    private static function pedido(PedidoOracao $p): array
    {
        return [
            'id' => $p->id,
            'nome' => $p->nome,
            'pedido' => $p->pedido,
            'anonimo' => (bool) $p->anonimo,
            'compartilhar' => (bool) $p->compartilhar,
            'status' => $p->status,
            'criado_em' => $p->created_at?->toIso8601String(),
        ];
    }
}
