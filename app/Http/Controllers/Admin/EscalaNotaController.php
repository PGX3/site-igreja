<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Escala;
use App\Models\EscalaNota;
use Illuminate\Http\Request;

class EscalaNotaController extends Controller
{
    public function store(Request $request, Escala $escala)
    {
        $this->authorizeEscala($escala);

        $data = $request->validate([
            'titulo' => 'nullable|string|max:200',
            'corpo' => 'required|string',
        ], [], ['corpo' => 'texto']);

        $escala->notas()->create([
            'titulo' => $data['titulo'] ?? null,
            'corpo' => $data['corpo'],
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Nota adicionada!');
    }

    public function destroy(Escala $escala, EscalaNota $nota)
    {
        $this->authorizeEscala($escala);
        abort_unless($nota->escala_id === $escala->id, 404);

        $nota->delete();

        return back()->with('success', 'Nota removida.');
    }

    private function authorizeEscala(Escala $escala): void
    {
        abort_unless(auth()->user()->canManageGrupo($escala->grupo_id), 403);
    }
}
