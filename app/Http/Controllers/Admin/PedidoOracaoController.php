<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PedidoOracao;

class PedidoOracaoController extends Controller
{
    public function index()
    {
        $pedidos = PedidoOracao::orderByDesc('created_at')->get();

        return inertia('Admin/PedidosOracao', ['pedidos' => $pedidos]);
    }

    public function marcarLido(PedidoOracao $pedido)
    {
        $pedido->update(['lido' => ! $pedido->lido]);

        return back();
    }

    public function destroy(PedidoOracao $pedido)
    {
        $pedido->delete();

        return back();
    }
}
