<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Musica;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MusicaController extends Controller
{
    public function index()
    {
        $musicas = Musica::with('createdBy:id,name')
            ->orderBy('nome')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'nome' => $m->nome,
                'tom' => $m->tom,
                'letra' => $m->letra,
                'link' => $m->link,
                'created_by' => $m->createdBy?->only('id', 'name'),
                'created_at' => $m->created_at->format('d/m/Y'),
            ]);

        return Inertia::render('Admin/Musicas/Index', compact('musicas'));
    }

    public function create()
    {
        return Inertia::render('Admin/Musicas/Form');
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
