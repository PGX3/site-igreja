<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EscalaMembro;
use App\Services\EscalaConfirmacaoService;
use App\Services\MinhasEscalasQuery;
use Illuminate\Auth\Access\AuthorizationException;
use Inertia\Inertia;

class EscalaMembroController extends Controller
{
    public function index(MinhasEscalasQuery $query)
    {
        $user = auth()->user();

        $escalas = $query->get($user)->map(fn ($e) => [
            'id'            => $e->id,
            'titulo'        => $e->titulo,
            'data'          => $e->data?->format('Y-m-d'),
            'hora_inicio'   => $e->hora_inicio,
            'hora_fim'      => $e->hora_fim,
            'grupo'         => $e->grupo?->only('id', 'nome'),
            'status_escala' => $e->status,
            'status_membro' => $e->pivot->status,
            'funcao'        => $e->pivot->funcao,
            'confirmado_em' => $e->pivot->confirmado_em
                ? \Carbon\Carbon::parse($e->pivot->confirmado_em)->format('d/m/Y H:i')
                : null,
            'pivot_id'      => EscalaMembro::where('escala_id', $e->id)
                ->where('user_id', $user->id)
                ->value('id'),
        ]);

        return Inertia::render('Admin/MinhasEscalas', compact('escalas'));
    }

    public function confirmar(EscalaMembro $escalaMembro, EscalaConfirmacaoService $service)
    {
        try {
            $service->confirmar($escalaMembro, auth()->user());
        } catch (AuthorizationException) {
            abort(403);
        }

        return back()->with('success', 'Presença confirmada!');
    }

    public function recusar(EscalaMembro $escalaMembro, EscalaConfirmacaoService $service)
    {
        try {
            $service->recusar($escalaMembro, auth()->user());
        } catch (AuthorizationException) {
            abort(403);
        }

        return back()->with('success', 'Participação recusada.');
    }
}
