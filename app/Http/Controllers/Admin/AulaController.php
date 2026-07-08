<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aula;
use App\Models\CursoModulo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AulaController extends Controller
{
    public function store(Request $request, CursoModulo $modulo)
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:150',
        ]);

        $aula = $modulo->aulas()->create([
            'titulo' => $data['titulo'],
            'ordem' => ($modulo->aulas()->max('ordem') ?? 0) + 1,
        ]);

        return redirect()->route('admin.aulas.edit', $aula)->with('success', 'Aula criada! Escreva o conteúdo.');
    }

    public function edit(Aula $aula)
    {
        $aula->load('modulo.curso');

        return Inertia::render('Admin/Cursos/AulaForm', [
            'aula' => [
                'id' => $aula->id,
                'titulo' => $aula->titulo,
                'tipo' => $aula->tipo,
                'conteudo' => $aula->conteudo,
                'youtube_url' => $aula->youtube_url,
                'ativo' => $aula->ativo,
            ],
            'curso' => [
                'id' => $aula->modulo->curso->id,
                'titulo' => $aula->modulo->curso->titulo,
            ],
            'modulo' => [
                'id' => $aula->modulo->id,
                'titulo' => $aula->modulo->titulo,
            ],
        ]);
    }

    public function update(Request $request, Aula $aula)
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:150',
            'tipo' => 'required|in:'.implode(',', Aula::TIPOS),
            'conteudo' => 'nullable|string',
            'youtube_url' => 'nullable|url|max:255',
            'ativo' => 'boolean',
        ]);

        $aula->update($data);

        return redirect()
            ->route('admin.cursos.conteudo', $aula->modulo->curso_id)
            ->with('success', 'Aula salva!');
    }

    public function destroy(Aula $aula)
    {
        $aula->delete();

        return back()->with('success', 'Aula removida.');
    }

    public function reordenar(Request $request, CursoModulo $modulo)
    {
        $data = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        foreach ($data['ids'] as $ordem => $id) {
            $modulo->aulas()->where('id', $id)->update(['ordem' => $ordem]);
        }

        return back();
    }

    public function compartilhar(Aula $aula)
    {
        $aula->gerarShareToken();

        return back()->with('success', 'Link da aula gerado.');
    }

    public function revogar(Aula $aula)
    {
        $aula->revogarLink();

        return back()->with('success', 'Link da aula revogado.');
    }
}
