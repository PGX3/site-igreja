<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EscalaMembro;
use Inertia\Inertia;

class EscalaMembroController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $escalas = $user->escalas()
            ->with('grupo')
            ->latest('data')
            ->get()
            ->map(fn($e) => [
                'id'           => $e->id,
                'titulo'       => $e->titulo,
                'data'         => $e->data?->format('Y-m-d'),
                'hora_inicio'  => $e->hora_inicio,
                'hora_fim'     => $e->hora_fim,
                'grupo'        => $e->grupo?->only('id', 'nome'),
                'status_escala' => $e->status,
                'status_membro' => $e->pivot->status,
                'funcao'       => $e->pivot->funcao,
                'confirmado_em' => $e->pivot->confirmado_em
                    ? \Carbon\Carbon::parse($e->pivot->confirmado_em)->format('d/m/Y H:i')
                    : null,
                'pivot_id'     => EscalaMembro::where('escala_id', $e->id)
                    ->where('user_id', $user->id)
                    ->value('id'),
            ]);

        return Inertia::render('Admin/MinhasEscalas', compact('escalas'));
    }

    public function confirmar(EscalaMembro $escalaMembro)
    {
        if ($escalaMembro->user_id !== auth()->id()) {
            abort(403);
        }

        $escalaMembro->update([
            'status'       => 'confirmado',
            'confirmado_em' => now(),
        ]);

        return back()->with('success', 'Presença confirmada!');
    }

    public function recusar(EscalaMembro $escalaMembro)
    {
        if ($escalaMembro->user_id !== auth()->id()) {
            abort(403);
        }

        $escalaMembro->update(['status' => 'recusado']);

        return back()->with('success', 'Participação recusada.');
    }
}
