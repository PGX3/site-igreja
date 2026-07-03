<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EscalaSetlistItem;
use App\Models\Musica;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MusicaController extends Controller
{
    public function index()
    {
        // Histórico: quantas vezes cada música foi tocada e a última data
        $ultimas = EscalaSetlistItem::join('escalas', 'escalas.id', '=', 'escala_setlist_itens.escala_id')
            ->selectRaw('escala_setlist_itens.musica_id, max(escalas.data) as ultima')
            ->groupBy('escala_setlist_itens.musica_id')
            ->pluck('ultima', 'musica_id');

        $musicas = Musica::with('createdBy:id,name')
            ->withCount('setlistItens')
            ->orderBy('nome')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'nome' => $m->nome,
                'tom' => $m->tom,
                'letra' => $m->letra,
                'link' => $m->link,
                'vezes' => $m->setlist_itens_count,
                'ultima' => isset($ultimas[$m->id])
                    ? \Carbon\Carbon::parse($ultimas[$m->id])->format('d/m/Y')
                    : null,
                'created_by' => $m->createdBy?->only('id', 'name'),
                'created_at' => $m->created_at->format('d/m/Y'),
            ]);

        return Inertia::render('Admin/Musicas/Index', compact('musicas'));
    }

    public function create()
    {
        return Inertia::render('Admin/Musicas/Form');
    }

    public function show(Musica $musica)
    {
        $musica->load('createdBy:id,name');

        $historico = $musica->setlistItens()
            ->with(['escala.grupo:id,nome'])
            ->get()
            ->filter(fn ($i) => $i->escala)
            ->sortByDesc(fn ($i) => $i->escala->data)
            ->map(fn ($i) => [
                'escala_id' => $i->escala->id,
                'titulo' => $i->escala->titulo,
                'data' => $i->escala->data?->format('d/m/Y'),
                'grupo' => $i->escala->grupo?->nome,
                'tom' => $i->tom ?: $musica->tom,
            ])->values();

        return Inertia::render('Admin/Musicas/Show', [
            'musica' => [
                'id' => $musica->id,
                'nome' => $musica->nome,
                'tom' => $musica->tom,
                'letra' => $musica->letra,
                'link' => $musica->link,
                'vezes' => $historico->count(),
                'ultima' => $historico->first()['data'] ?? null,
                'created_by' => $musica->createdBy?->only('id', 'name'),
                'created_at' => $musica->created_at->format('d/m/Y'),
            ],
            'historico' => $historico,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        Musica::create(array_merge($data, ['created_by' => auth()->id()]));

        return redirect()->route('admin.musicas.index')->with('success', 'Música adicionada!');
    }

    public function edit(Musica $musica)
    {
        return Inertia::render('Admin/Musicas/Form', [
            'musica' => [
                'id' => $musica->id,
                'nome' => $musica->nome,
                'tom' => $musica->tom,
                'letra' => $musica->letra,
                'link' => $musica->link,
            ],
        ]);
    }

    public function update(Request $request, Musica $musica)
    {
        $data = $this->validateData($request);

        $musica->update($data);

        return redirect()->route('admin.musicas.index')->with('success', 'Música atualizada!');
    }

    public function imprimir(Musica $musica)
    {
        return view('print.musica', ['musica' => $musica]);
    }

    public function destroy(Musica $musica)
    {
        $musica->delete();

        return redirect()->route('admin.musicas.index')->with('success', 'Música removida.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'nome' => 'required|string|max:200',
            'tom' => 'nullable|string|max:10',
            'letra' => 'required|string',
            'link' => 'nullable|url|max:500',
        ], [], [
            'nome' => 'nome',
            'letra' => 'letra',
            'link' => 'link',
        ]);
    }
}
