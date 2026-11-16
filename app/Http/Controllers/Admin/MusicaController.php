<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EscalaSetlistItem;
use App\Models\Musica;
use App\Services\HtmlSanitizer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
                'artista' => $m->artista,
                'tom' => $m->tom,
                'bpm' => $m->bpm,
                'categoria' => $m->categoria,
                'letra' => $m->letra,
                'link' => $m->link,
                'vezes' => $m->setlist_itens_count,
                'ultima' => isset($ultimas[$m->id])
                    ? Carbon::parse($ultimas[$m->id])->format('d/m/Y')
                    : null,
                'created_by' => $m->createdBy?->only('id', 'name'),
                'created_at' => $m->created_at->format('d/m/Y'),
            ]);

        return Inertia::render('Admin/Musicas/Index', compact('musicas'));
    }

    public function create()
    {
        return Inertia::render('Admin/Musicas/Form', [
            'categorias' => Musica::CATEGORIAS,
            'tipos_secao' => Musica::TIPOS_SECAO,
        ]);
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
                'artista' => $musica->artista,
                'tom' => $musica->tom,
                'bpm' => $musica->bpm,
                'categoria' => Musica::CATEGORIAS[$musica->categoria] ?? null,
                'letra' => $musica->letra,
                'letra_crua' => $musica->letraCrua(),
                'secoes_publico' => collect($musica->secoes_publico)->map(fn ($s) => [
                    'tipo' => $s['tipo'],
                    'rotulo' => Musica::TIPOS_SECAO[$s['tipo']] ?? $s['tipo'],
                    'texto' => $s['texto'],
                ])->all(),
                'letra_publico' => $musica->letraPublicoTexto(),
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
                'artista' => $musica->artista,
                'tom' => $musica->tom,
                'bpm' => $musica->bpm,
                'categoria' => $musica->categoria,
                'letra' => $musica->letra,
                'secoes_publico' => $musica->secoes_publico ?? [],
                'link' => $musica->link,
            ],
            'categorias' => Musica::CATEGORIAS,
            'tipos_secao' => Musica::TIPOS_SECAO,
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
        $data = $request->validate([
            'nome' => 'required|string|max:200',
            'artista' => 'nullable|string|max:150',
            'tom' => 'nullable|string|max:10',
            'bpm' => 'nullable|integer|between:20,400',
            'categoria' => ['nullable', Rule::in(array_keys(Musica::CATEGORIAS))],
            'letra' => 'nullable|string',
            'secoes_publico' => 'nullable|array',
            'secoes_publico.*.tipo' => ['nullable', Rule::in(array_keys(Musica::TIPOS_SECAO))],
            'secoes_publico.*.texto' => 'nullable|string',
            'link' => 'nullable|url|max:500',
        ], [], [
            'nome' => 'nome',
            'letra' => 'letra',
            'link' => 'link',
        ]);

        // Letra é opcional: dá para cadastrar só pelo nome e completar depois.
        // A coluna não aceita null (e o app espera texto), então vazio vira ''.
        $data['letra'] = HtmlSanitizer::clean($data['letra'] ?? '');
        $data['secoes_publico'] = Musica::limparSecoes($data['secoes_publico'] ?? null);

        return $data;
    }
}
