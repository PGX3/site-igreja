<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grupo;
use App\Models\GrupoFuncao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GrupoFuncaoController extends Controller
{
    public function store(Request $request, Grupo $grupo): RedirectResponse
    {
        if (! $request->user()->canManageGrupo($grupo->id)) {
            abort(403);
        }

        $data = $request->validate([
            'nome' => [
                'required', 'string', 'max:100',
                Rule::unique('grupo_funcoes', 'nome')->where(fn ($q) => $q->where('grupo_id', $grupo->id)),
            ],
        ], [], ['nome' => 'nome da função']);

        GrupoFuncao::create([
            'grupo_id' => $grupo->id,
            'nome' => $data['nome'],
        ]);

        return back()->with('success', 'Função adicionada.');
    }

    public function destroy(Request $request, Grupo $grupo, GrupoFuncao $funcao): RedirectResponse
    {
        if (! $request->user()->canManageGrupo($grupo->id)) {
            abort(403);
        }

        if ($funcao->grupo_id !== $grupo->id) {
            abort(404);
        }

        $funcao->delete();

        return back()->with('success', 'Função removida.');
    }

    /** Marca quem do grupo faz esta função (usado na sugestão de escala). */
    public function membros(Request $request, Grupo $grupo, GrupoFuncao $funcao): RedirectResponse
    {
        if (! $request->user()->canManageGrupo($grupo->id)) {
            abort(403);
        }

        if ($funcao->grupo_id !== $grupo->id) {
            abort(404);
        }

        $data = $request->validate([
            'membros_ids' => 'present|array',
            'membros_ids.*' => 'integer',
        ]);

        // Só quem é membro do grupo pode ter função nele
        $funcao->membros()->sync(
            $grupo->membros()->whereIn('users.id', $data['membros_ids'])->pluck('users.id')->all()
        );

        return back();
    }
}
