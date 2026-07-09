<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aula;
use App\Models\AulaComentario;
use Illuminate\Http\Request;

class AulaComentarioController extends Controller
{
    public function store(Request $request, Aula $aula)
    {
        $gestor = $this->ehGestor();
        abort_unless($aula->ativo || $gestor, 404);

        $data = $request->validate([
            'corpo' => 'required|string|max:2000',
        ]);

        $aula->comentarios()->create([
            'user_id' => auth()->id(),
            'corpo' => $data['corpo'],
        ]);

        return back()->with('success', 'Comentário enviado.');
    }

    public function destroy(AulaComentario $comentario)
    {
        abort_unless($comentario->user_id === auth()->id() || $this->ehGestor(), 403);

        $comentario->delete();

        return back()->with('success', 'Comentário removido.');
    }

    private function ehGestor(): bool
    {
        $u = auth()->user();

        return $u && ($u->isPastor() || $u->isLider());
    }
}
