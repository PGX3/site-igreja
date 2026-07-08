<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\CursoModulo;
use Illuminate\Http\Request;

class CursoModuloController extends Controller
{
    public function store(Request $request, Curso $curso)
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:150',
            'descricao' => 'nullable|string|max:255',
        ]);

        $curso->modulos()->create([
            'titulo' => $data['titulo'],
            'descricao' => $data['descricao'] ?? null,
            'ordem' => ($curso->modulos()->max('ordem') ?? 0) + 1,
        ]);

        return back()->with('success', 'Módulo adicionado!');
    }

    public function update(Request $request, CursoModulo $modulo)
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:150',
            'descricao' => 'nullable|string|max:255',
        ]);

        $modulo->update($data);

        return back()->with('success', 'Módulo atualizado!');
    }

    public function destroy(CursoModulo $modulo)
    {
        $modulo->delete();

        return back()->with('success', 'Módulo removido.');
    }

    public function reordenar(Request $request, Curso $curso)
    {
        $data = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        foreach ($data['ids'] as $ordem => $id) {
            $curso->modulos()->where('id', $id)->update(['ordem' => $ordem]);
        }

        return back();
    }
}
