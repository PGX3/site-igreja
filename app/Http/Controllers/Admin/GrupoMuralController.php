<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grupo;
use App\Models\GrupoAviso;
use App\Models\GrupoMusica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            ->map(fn($a) => [
                'id'         => $a->id,
                'titulo'     => $a->titulo,
                'corpo'      => $a->corpo,
                'fixado'     => $a->fixado,
                'autor'      => $a->autor?->only('id', 'name'),
                'created_at' => $a->created_at->format('d/m/Y H:i'),
            ]);

        $musicas = $grupo->musicas()
            ->with('uploadedBy:id,name')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($m) => [
                'id'           => $m->id,
                'nome'         => $m->nome,
                'tom'          => $m->tom,
                'letra'        => $m->letra,
                'tem_arquivo'  => (bool) $m->arquivo_path,
                'uploaded_by'  => $m->uploadedBy?->only('id', 'name'),
                'created_at'   => $m->created_at->format('d/m/Y'),
            ]);

        return Inertia::render('Admin/Grupos/Mural', [
            'grupo'    => ['id' => $grupo->id, 'nome' => $grupo->nome, 'tem_musicas' => $grupo->tem_musicas],
            'avisos'   => $avisos,
            'musicas'  => $musicas,
            'can_manage' => $user->canManageGrupo($grupo->id),
        ]);
    }

    public function storeAviso(Request $request, Grupo $grupo)
    {
        abort_unless(auth()->user()->canManageGrupo($grupo->id), 403);

        $data = $request->validate([
            'titulo' => 'required|string|max:200',
            'corpo'  => 'required|string',
            'fixado' => 'boolean',
        ]);

        $grupo->avisos()->create([
            'autor_id' => auth()->id(),
            'titulo'   => $data['titulo'],
            'corpo'    => $data['corpo'],
            'fixado'   => $data['fixado'] ?? false,
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

    public function storeMusica(Request $request, Grupo $grupo)
    {
        abort_unless($grupo->tem_musicas, 403);
        abort_unless(auth()->user()->canManageGrupo($grupo->id), 403);

        $data = $request->validate([
            'nome'    => 'required|string|max:200',
            'tom'     => 'nullable|string|max:10',
            'letra'   => 'nullable|string',
            'arquivo' => 'nullable|file|mimes:pdf|max:102400',
        ]);

        if (empty($data['letra']) && !$request->hasFile('arquivo')) {
            return back()->withErrors(['arquivo' => 'Adicione um PDF ou digite a letra da música.']);
        }

        $path = $request->hasFile('arquivo')
            ? $request->file('arquivo')->store("grupos/{$grupo->id}/musicas", 'public')
            : null;

        $grupo->musicas()->create([
            'uploaded_by'  => auth()->id(),
            'nome'         => $data['nome'],
            'tom'          => $data['tom'] ?? null,
            'letra'        => $data['letra'] ?? null,
            'arquivo_path' => $path,
        ]);

        return back()->with('success', 'Música adicionada!');
    }

    public function destroyMusica(Grupo $grupo, GrupoMusica $musica)
    {
        abort_unless($grupo->tem_musicas, 403);
        abort_unless(auth()->user()->canManageGrupo($grupo->id), 403);
        abort_unless($musica->grupo_id === $grupo->id, 404);

        Storage::disk('public')->delete($musica->arquivo_path);
        $musica->delete();

        return back()->with('success', 'Música removida.');
    }

    public function downloadMusica(Grupo $grupo, GrupoMusica $musica)
    {
        $user = auth()->user();
        $isMembro = $user->isPastor() || $grupo->membros()->where('users.id', $user->id)->exists();
        abort_unless($isMembro, 403);
        abort_unless($musica->grupo_id === $grupo->id, 404);

        return Storage::disk('public')->download($musica->arquivo_path, $musica->nome . '.pdf');
    }
}
