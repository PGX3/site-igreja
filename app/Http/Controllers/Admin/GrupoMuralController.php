<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grupo;
use App\Models\GrupoAviso;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GrupoMuralController extends Controller
{
    public function show(Grupo $grupo)
    {
        $user = auth()->user();

        $isMembro = $user->isPastor() || $grupo->membros()->where('users.id', $user->id)->exists();

        abort_unless($isMembro, 403);

        $avisos = $grupo->avisos()
            ->with('autor:id,name')
            ->orderByDesc('fixado')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'titulo' => $a->titulo,
                'corpo' => $a->corpo,
                'fixado' => $a->fixado,
                'autor' => $a->autor?->only('id', 'name'),
                'created_at' => $a->created_at->format('d/m/Y H:i'),
            ]);

        return Inertia::render('Admin/Grupos/Mural', [
            'grupo' => ['id' => $grupo->id, 'nome' => $grupo->nome, 'tem_musicas' => $grupo->tem_musicas],
            'avisos' => $avisos,
            'can_manage' => $user->canManageGrupo($grupo->id),
        ]);
    }

    public function storeAviso(Request $request, Grupo $grupo)
    {
        abort_unless(auth()->user()->canManageGrupo($grupo->id), 403);

        $data = $request->validate([
            'titulo' => 'required|string|max:200',
            'corpo' => 'required|string',
            'fixado' => 'boolean',
        ]);

        $grupo->avisos()->create([
            'autor_id' => auth()->id(),
            'titulo' => $data['titulo'],
            'corpo' => $data['corpo'],
            'fixado' => $data['fixado'] ?? false,
        ]);

        return back()->with('success', 'Aviso publicado!');
    }

    public function destroyAviso(Grupo $grupo, GrupoAviso $aviso)
    {
        abort_unless(auth()->user()->canManageGrupo($grupo->id), 403);
        abort_unless($aviso->grupo_id === $grupo->id, 404);

        $aviso->delete();

        return back()->with('success', 'Aviso removido.');
    }
}
