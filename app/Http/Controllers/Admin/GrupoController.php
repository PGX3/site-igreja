<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grupo;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GrupoController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $query = Grupo::with(['lider', 'membros'])->withCount('escalas')->latest();

        if (! $user->isPastor()) {
            $query->whereHas('membros', fn ($q) => $q->where('users.id', $user->id));
        }

        $grupos = $query->get()->map(fn ($g) => [
            'id' => $g->id,
            'nome' => $g->nome,
            'descricao' => $g->descricao,
            'lider' => $g->lider?->only('id', 'name'),
            'total_membros' => $g->membros->count(),
            'total_escalas' => $g->escalas_count,
        ]);

        return Inertia::render('Admin/Grupos/Index', [
            'grupos' => $grupos,
            'can_create' => $user->isPastor(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Grupos/Form', [
            'usuarios' => $this->usuariosDisponiveis(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $grupo = Grupo::create([
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
            'tem_musicas' => $data['tem_musicas'] ?? false,
            'lider_id' => $data['lider_id'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $this->sincronizarMembros($grupo, $data['membros_ids'] ?? []);
        $this->aplicarRoleLider($grupo, null);

        return redirect()->route('admin.grupos.index')
            ->with('success', 'Grupo criado com sucesso!');
    }

    public function edit(Grupo $grupo)
    {
        $grupo->load(['lider', 'membros:id']);

        return Inertia::render('Admin/Grupos/Form', [
            'grupo' => [
                'id' => $grupo->id,
                'nome' => $grupo->nome,
                'descricao' => $grupo->descricao,
                'tem_musicas' => $grupo->tem_musicas,
                'lider' => $grupo->lider?->only('id', 'name'),
                'membros_ids' => $grupo->membros->pluck('id')->all(),
            ],
            'usuarios' => $this->usuariosDisponiveis(),
        ]);
    }

    public function update(Request $request, Grupo $grupo)
    {
        $data = $this->validateData($request);

        $oldLiderId = $grupo->lider_id;
        $grupo->update([
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
            'tem_musicas' => $data['tem_musicas'] ?? false,
            'lider_id' => $data['lider_id'] ?? null,
        ]);

        $this->sincronizarMembros($grupo, $data['membros_ids'] ?? []);
        $this->aplicarRoleLider($grupo, $oldLiderId);

        return redirect()->route('admin.grupos.index')
            ->with('success', 'Grupo atualizado!');
    }

    public function destroy(Grupo $grupo)
    {
        $grupo->delete();

        return redirect()->route('admin.grupos.index')
            ->with('success', 'Grupo excluído!');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'nome' => 'required|string|max:100',
            'descricao' => 'nullable|string',
            'tem_musicas' => 'boolean',
            'lider_id' => 'nullable|exists:users,id',
            'membros_ids' => 'nullable|array',
            'membros_ids.*' => 'integer|exists:users,id',
        ]);
    }

    private function sincronizarMembros(Grupo $grupo, array $membrosIds): void
    {
        $ids = collect($membrosIds)->map(fn ($id) => (int) $id);

        if ($grupo->lider_id) {
            $ids = $ids->push($grupo->lider_id);
        }

        $grupo->membros()->sync($ids->unique()->values()->all());
    }

    private function aplicarRoleLider(Grupo $grupo, ?int $oldLiderId): void
    {
        $liderRoleId = Role::where('name', 'lider')->value('id');
        $membroRoleId = Role::where('name', 'membro')->value('id');

        if ($oldLiderId && $oldLiderId !== $grupo->lider_id) {
            $oldLider = User::find($oldLiderId);
            if ($oldLider && ! $oldLider->isPastor()) {
                $oldLider->update(['role_id' => $membroRoleId]);
            }
        }

        if ($grupo->lider_id) {
            $novoLider = User::find($grupo->lider_id);
            if ($novoLider && ! $novoLider->isPastor()) {
                $novoLider->update(['role_id' => $liderRoleId]);
            }
        }
    }

    private function usuariosDisponiveis()
    {
        return User::where('is_superadmin', false)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
