<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Culto;
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
            'cultos' => $this->cultosAtivos(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $grupo = Grupo::create([
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
            'tem_musicas' => $data['tem_musicas'] ?? false,
            'escala_eventos' => $data['escala_eventos'] ?? true,
            'whatsapp_apikey' => $data['whatsapp_apikey'] ?? null,
            'whatsapp_phone' => $data['whatsapp_phone'] ?? null,
            'lider_id' => $data['lider_id'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $this->sincronizarMembros($grupo, $data['membros_ids'] ?? []);
        $this->sincronizarCultos($grupo, $data['cultos_ids'] ?? null);
        $this->aplicarRoleLider($grupo, null);

        return redirect()->route('admin.grupos.index')
            ->with('success', 'Grupo criado com sucesso!');
    }

    public function edit(Grupo $grupo)
    {
        $grupo->load(['lider', 'membros:id', 'funcoes:id,grupo_id,nome', 'cultosSemEscala:id']);
        $cultos = $this->cultosAtivos();
        $semEscala = $grupo->cultosSemEscala->pluck('id');

        return Inertia::render('Admin/Grupos/Form', [
            'grupo' => [
                'id' => $grupo->id,
                'nome' => $grupo->nome,
                'descricao' => $grupo->descricao,
                'tem_musicas' => $grupo->tem_musicas,
                'escala_eventos' => $grupo->escala_eventos,
                'cultos_ids' => $cultos->pluck('id')->diff($semEscala)->values()->all(),
                'whatsapp_apikey' => $grupo->whatsapp_apikey,
                'whatsapp_phone' => $grupo->whatsapp_phone,
                'lider' => $grupo->lider?->only('id', 'name'),
                'membros_ids' => $grupo->membros->pluck('id')->all(),
                'funcoes' => $grupo->funcoes->map(fn ($f) => ['id' => $f->id, 'nome' => $f->nome])->values(),
            ],
            'usuarios' => $this->usuariosDisponiveis(),
            'cultos' => $cultos,
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
            'escala_eventos' => $data['escala_eventos'] ?? true,
            'whatsapp_apikey' => $data['whatsapp_apikey'] ?? null,
            'whatsapp_phone' => $data['whatsapp_phone'] ?? null,
            'lider_id' => $data['lider_id'] ?? null,
        ]);

        $this->sincronizarMembros($grupo, $data['membros_ids'] ?? []);
        $this->sincronizarCultos($grupo, $data['cultos_ids'] ?? null);
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
            'escala_eventos' => 'boolean',
            'cultos_ids' => 'nullable|array',
            'cultos_ids.*' => 'integer|exists:cultos,id',
            'whatsapp_apikey' => 'nullable|string|max:100',
            'whatsapp_phone' => 'nullable|string|max:30',
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

    /**
     * Recebe os cultos marcados (com escala) e guarda os desmarcados como exceção.
     * Só olha os cultos ativos: um culto desativado mantém o que já estava.
     * Sem cultos_ids na requisição (ex.: cliente antigo), não mexe em nada.
     */
    private function sincronizarCultos(Grupo $grupo, ?array $cultosIds): void
    {
        if ($cultosIds === null) {
            return;
        }

        $ativos = Culto::where('ativo', true)->pluck('id');
        $marcados = collect($cultosIds)->map(fn ($id) => (int) $id);
        $inativosSemEscala = $grupo->cultosSemEscala()->where('ativo', false)->pluck('cultos.id');

        $grupo->cultosSemEscala()->sync(
            $ativos->diff($marcados)->merge($inativosSemEscala)->unique()->values()->all()
        );
    }

    private function cultosAtivos()
    {
        $ordem = ['Domingo' => 0, 'Segunda' => 1, 'Terça' => 2, 'Quarta' => 3, 'Quinta' => 4, 'Sexta' => 5, 'Sábado' => 6];

        return Culto::where('ativo', true)->get(['id', 'nome', 'dia_semana', 'horario'])
            ->sortBy(fn ($c) => [$ordem[$c->dia_semana] ?? 7, $c->horario])
            ->values()
            ->map(fn ($c) => ['id' => $c->id, 'nome' => $c->nome, 'dia_semana' => $c->dia_semana, 'horario' => $c->horario]);
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
