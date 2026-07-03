<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Escala;
use App\Models\EscalaSetlistItem;
use Illuminate\Http\Request;

class EscalaSetlistController extends Controller
{
    public function imprimir(Escala $escala)
    {
        $this->authorizeEscala($escala);

        $escala->load(['grupo:id,nome', 'setlist.musica']);

        return view('print.setlist', ['escala' => $escala]);
    }

    public function store(Request $request, Escala $escala)
    {
        $this->authorizeEscala($escala);

        $data = $request->validate([
            'musica_id' => 'required|exists:musicas,id',
            'tom' => 'nullable|string|max:10',
            'observacao' => 'nullable|string|max:255',
        ], [], [
            'musica_id' => 'música',
        ]);

        $jaExiste = $escala->setlist()->where('musica_id', $data['musica_id'])->exists();
        if ($jaExiste) {
            return back()->withErrors(['musica_id' => 'Essa música já está no setlist.']);
        }

        $escala->setlist()->create([
            'musica_id' => $data['musica_id'],
            'tom' => $data['tom'] ?? null,
            'observacao' => $data['observacao'] ?? null,
            'ordem' => (int) $escala->setlist()->max('ordem') + 1,
        ]);

        return back()->with('success', 'Música adicionada ao setlist!');
    }

    public function update(Request $request, Escala $escala, EscalaSetlistItem $item)
    {
        $this->authorizeEscala($escala);
        abort_unless($item->escala_id === $escala->id, 404);

        $data = $request->validate([
            'tom' => 'nullable|string|max:10',
            'observacao' => 'nullable|string|max:255',
        ]);

        $item->update([
            'tom' => $data['tom'] ?? null,
            'observacao' => $data['observacao'] ?? null,
        ]);

        return back()->with('success', 'Setlist atualizado!');
    }

    public function reorder(Request $request, Escala $escala)
    {
        $this->authorizeEscala($escala);

        $data = $request->validate([
            'itens' => 'required|array',
            'itens.*' => 'integer',
        ]);

        foreach ($data['itens'] as $ordem => $id) {
            $escala->setlist()->where('id', $id)->update(['ordem' => $ordem]);
        }

        return back();
    }

    public function destroy(Escala $escala, EscalaSetlistItem $item)
    {
        $this->authorizeEscala($escala);
        abort_unless($item->escala_id === $escala->id, 404);

        $item->delete();

        return back()->with('success', 'Música removida do setlist.');
    }

    private function authorizeEscala(Escala $escala): void
    {
        abort_unless(auth()->user()->canManageGrupo($escala->grupo_id), 403);
        abort_unless($escala->grupo?->tem_musicas, 403);
    }
}
