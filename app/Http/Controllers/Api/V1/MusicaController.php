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
            ->get(['id', 'nome', 'artista', 'tom', 'bpm', 'categoria', 'link'])
            ->map(fn (Musica $m) => $m->only('id', 'nome', 'artista', 'tom', 'bpm', 'categoria', 'link'));

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
                'artista' => $musica->artista,
                'tom' => $musica->tom,
                'bpm' => $musica->bpm,
                'categoria' => $musica->categoria,
                'letra' => $musica->letraComCores(),
                'letra_crua' => $musica->letraCrua(),
                'secoes_publico' => $musica->secoes_publico ?? [],
                'link' => $musica->link,
                'vezes' => $historico->count(),
                'historico' => $historico,
            ],
        ]);
    }
}
