<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PedidoOracao;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PedidoOracaoController extends Controller
{
    public function index()
    {
        $pedidos = PedidoOracao::orderByDesc('created_at')->get();

        return inertia('Admin/PedidosOracao', ['pedidos' => $pedidos]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'anonimo' => ['boolean'],
            'nome' => ['nullable', 'required_if:anonimo,false', 'string', 'max:100'],
            'pedido' => ['required', 'string', 'max:2000'],
            'compartilhar' => ['boolean'],
        ]);

        $anonimo = (bool) ($validated['anonimo'] ?? false);

        PedidoOracao::create([
            'nome' => $anonimo ? 'Anônimo' : $validated['nome'],
            'pedido' => $validated['pedido'],
            'anonimo' => $anonimo,
            'compartilhar' => (bool) ($validated['compartilhar'] ?? false),
            'status' => PedidoOracao::STATUS_NOVO,
        ]);

        return back()->with('success', 'Pedido de oração adicionado!');
    }

    public function atualizarStatus(Request $request, PedidoOracao $pedido)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(PedidoOracao::STATUSES)],
        ]);

        $pedido->update(['status' => $validated['status']]);

        return back();
    }

    public function destroy(PedidoOracao $pedido)
    {
        $pedido->delete();

        return back();
    }
}
