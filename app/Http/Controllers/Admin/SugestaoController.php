<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sugestao;

class SugestaoController extends Controller
{
    public function index()
    {
        $sugestoes = Sugestao::orderByDesc('created_at')->get();
        return inertia('Admin/Sugestoes', ['sugestoes' => $sugestoes]);
    }

    public function marcarLida(Sugestao $sugestao)
    {
        $sugestao->update(['lida' => !$sugestao->lida]);
        return back();
    }

    public function destroy(Sugestao $sugestao)
    {
        $sugestao->delete();
        return back();
    }
}
