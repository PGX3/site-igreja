<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Culto;
use App\Models\Texto;
use App\Models\Sugestao;
use App\Models\PedidoOracao;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'totalCultos'       => Culto::count(),
            'totalTextos'       => Texto::count(),
            'novasSugestoes'    => Sugestao::where('lida', false)->count(),
            'novosPedidos'      => PedidoOracao::where('lido', false)->count(),
            'totalSugestoes'    => Sugestao::count(),
            'totalPedidos'      => PedidoOracao::count(),
        ]);
    }
}
