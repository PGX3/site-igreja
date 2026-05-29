<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GrupoController extends Controller
{
    public function index()
    {
        $grupos = Grupo::with(['lider', 'membros'])
            ->withCount('escalas')
            ->latest()
            ->get()
            ->map(fn($g) => [
                'id'           => $g->id,
                'nome'         => $g->nome,
                'descricao'    => $g->descricao,
                'lider'        => $g->lider?->only('id', 'name'),
                'total_membros' => $g->membros->count(),
                'total_escalas' => $g->escalas_count,
            ]);

        return Inertia::render('Admin/Grupos/Index', compact('grupos'));
    }

    public function create()
    {
        $usuarios = User::orderBy('name')->get(['id', 'name', 'email']);
        return Inertia::render('Admin/Grupos/Form', compact('usuarios'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'      => 'required|string|max:100',
            'descricao' => 'nullable|string',
            'lider_id'  => 'nullable|exists:users,id',
        ]);

        $data['created_by'] = auth()->id();
        $grupo = Grupo::create($data);

        if ($grupo->lider_id) {
            $lider = User::find($grupo->lider_id);
            if ($lider && !$lider->isPastor()) {
                $lider->update([
                    'grupo_id' => $grupo->id,
                    'role_id'  => \App\Models\Role::where('name', 'lider')->value('id'),
                ]);
            }
        }

        return redirect()->route('admin.grupos.index')
            ->with('success', 'Grupo criado com sucesso!');
    }

    public function edit(Grupo $grupo)
    {
        $usuarios = User::orderBy('name')->get(['id', 'name', 'email']);
        return Inertia::render('Admin/Grupos/Form', [
            'grupo'    => $grupo->load('lider'),
            'usuarios' => $usuarios,
        ]);
    }

    public function update(Request $request, Grupo $grupo)
    {
        $data = $request->validate([
            'nome'      => 'required|string|max:100',
            'descricao' => 'nullable|string',
            'lider_id'  => 'nullable|exists:users,id',
        ]);

        $oldLiderId = $grupo->lider_id;
        $grupo->update($data);

        $liderRoleId  = \App\Models\Role::where('name', 'lider')->value('id');
        $membroRoleId = \App\Models\Role::where('name', 'membro')->value('id');

        // Rebaixa o líder anterior se mudou (nunca rebaixa pastor)
        if ($oldLiderId && $oldLiderId !== $grupo->lider_id) {
            $oldLider = User::find($oldLiderId);
            if ($oldLider && !$oldLider->isPastor()) {
                $oldLider->update(['role_id' => $membroRoleId, 'grupo_id' => null]);
            }
        }

        // Promove o novo líder (nunca altera pastor)
        if ($grupo->lider_id) {
            $novoLider = User::find($grupo->lider_id);
            if ($novoLider && !$novoLider->isPastor()) {
                $novoLider->update([
                    'grupo_id' => $grupo->id,
                    'role_id'  => $liderRoleId,
                ]);
            }
        }

        return redirect()->route('admin.grupos.index')
            ->with('success', 'Grupo atualizado!');
    }

    public function destroy(Grupo $grupo)
    {
        $grupo->delete();
        return redirect()->route('admin.grupos.index')
            ->with('success', 'Grupo excluído!');
    }
}
