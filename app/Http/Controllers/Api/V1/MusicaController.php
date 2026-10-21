<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Musica;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Repertório para os músicos (somente leitura, qualquer membro logado).
 * Cadastro e edição de músicas continuam no painel.
 */
class MusicaController extends Controller
{
    /** Lista sem a letra (fica leve). `?q=` busca pelo nome. */
    public function index(Request $request): JsonResponse
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100']]);

        $musicas = Musica::query()
            ->when($request->filled('q'), fn ($q) => $q->where('nome', 'like', '%'.$request->input('q').'%'))
            ->orderBy('nome')
            ->get(['id', 'nome', 'tom', 'link'])
            ->map(fn (Musica $m) => $m->only('id', 'nome', 'tom', 'link'));

        return response()->json(['data' => $musicas]);
    }

    /** Letra/cifra em HTML e o histórico de escalas em que foi tocada. */
    public function show(Musica $musica): JsonResponse
    {
        $historico = $musica->setlistItens()
            ->with('escala.grupo:id,nome')
            ->get()
            ->filter(fn ($i) => $i->escala)
            ->sortByDesc(fn ($i) => $i->escala->data)
            ->map(fn ($i) => [
                'escala_id' => $i->escala->id,
                'titulo' => $i->escala->titulo,
                'data' => $i->escala->data?->format('Y-m-d'),
                'grupo' => $i->escala->grupo?->nome,
                'tom' => $i->tom ?: $musica->tom,
            ])
            ->values();

        return response()->json([
            'data' => [
                'id' => $musica->id,
                'nome' => $musica->nome,
                'tom' => $musica->tom,
                'letra' => $musica->letra,
                'link' => $musica->link,
                'vezes' => $historico->count(),
                'historico' => $historico,
            ],
        ]);
    }
}
