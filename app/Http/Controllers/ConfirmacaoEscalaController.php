<?php

namespace App\Http\Controllers;

use App\Models\EscalaMembro;
use Illuminate\Support\Facades\URL;

class ConfirmacaoEscalaController extends Controller
{
    public function show(EscalaMembro $escalaMembro)
    {
        $escalaMembro->load(['escala.grupo', 'user']);

        return view('convite', [
            'em' => $escalaMembro,
            'escala' => $escalaMembro->escala,
            'feito' => null,
            'confirmarUrl' => URL::signedRoute('convite.responder', ['escalaMembro' => $escalaMembro->id, 'acao' => 'confirmar']),
            'recusarUrl' => URL::signedRoute('convite.responder', ['escalaMembro' => $escalaMembro->id, 'acao' => 'recusar']),
        ]);
    }

    public function responder(EscalaMembro $escalaMembro, string $acao)
    {
        $escalaMembro->load(['escala.grupo', 'user']);

        if ($acao === 'confirmar') {
            $escalaMembro->update(['status' => 'confirmado', 'confirmado_em' => now()]);
        } else {
            $escalaMembro->update(['status' => 'recusado', 'confirmado_em' => null]);
        }

        return view('convite', [
            'em' => $escalaMembro,
            'escala' => $escalaMembro->escala,
            'feito' => $acao,
        ]);
    }
}
