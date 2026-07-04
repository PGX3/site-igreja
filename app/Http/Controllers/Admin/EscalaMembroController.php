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

        $escalas = $query->get($user)
            ->load(['setlist.musica', 'assets', 'notas'])
            ->map(fn ($e) => [
                'id' => $e->id,
                'titulo' => $e->titulo,
                'data' => $e->data?->format('Y-m-d'),
                'hora_inicio' => $e->hora_inicio,
                'hora_fim' => $e->hora_fim,
                'grupo' => $e->grupo?->only('id', 'nome'),
                'status_escala' => $e->status,
                'status_membro' => $e->pivot->status,
                'funcao' => $e->pivot->funcao,
                'confirmado_em' => $e->pivot->confirmado_em
                    ? \Carbon\Carbon::parse($e->pivot->confirmado_em)->format('d/m/Y H:i')
                    : null,
                'pivot_id' => EscalaMembro::where('escala_id', $e->id)
                    ->where('user_id', $user->id)
                    ->value('id'),
                'setlist' => $e->setlist->filter(fn ($s) => $s->musica)->map(fn ($s) => [
                    'nome' => $s->musica->nome,
                    'tom' => $s->tom ?: $s->musica->tom,
                    'letra' => $s->musica->letra,
                    'link' => $s->musica->link,
                    'observacao' => $s->observacao,
                ])->values(),
                'anexos' => $e->assets->map(fn ($a) => [
                    'id' => $a->id,
                    'tipo' => $a->tipo,
                    'titulo' => $a->titulo,
                    'arquivo_path' => $a->arquivo_path,
                    'arquivo_nome' => $a->arquivo_nome,
                ])->values(),
                'notas' => $e->notas->map(fn ($n) => [
                    'id' => $n->id,
                    'titulo' => $n->titulo,
                    'corpo' => $n->corpo,
                ])->values(),
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
