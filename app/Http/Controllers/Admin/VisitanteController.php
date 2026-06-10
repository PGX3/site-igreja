<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Familia;
use App\Models\User;
use App\Support\Cpf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VisitanteController extends Controller
{
    public function index(Request $request)
    {
        $busca = trim((string) $request->query('busca', ''));

        $visitantes = User::visitantes()
            ->where('is_superadmin', false)
            ->when($busca !== '', fn($q) =>
                $q->where(fn($w) => $w
                    ->where('name', 'like', "%{$busca}%")
                    ->orWhere('telefone', 'like', "%{$busca}%")))
            ->with('convidadoPor:id,name')
            ->orderByDesc('primeira_visita')
            ->orderBy('name')
            ->get()
            ->map(fn($u) => [
                'id'              => $u->id,
                'name'            => $u->name,
                'telefone'        => $u->telefone,
                'como_conheceu'   => $u->como_conheceu,
                'convidado_por'   => $u->convidadoPor?->name,
                'primeira_visita' => optional($u->primeira_visita)->format('Y-m-d'),
            ]);

        return Inertia::render('Admin/Visitantes/Index', [
            'visitantes' => $visitantes,
            'busca'      => $busca,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Visitantes/Form', [
            'convidadores' => $this->convidadoresOptions(),
            'familias'     => $this->familiasOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['tipo'] = 'visitante';

        User::create($data);

        return redirect()->route('admin.visitantes.index')
            ->with('success', 'Visitante cadastrado!');
    }

    public function edit(User $visitante)
    {
        abort_unless($visitante->tipo === 'visitante', 404);

        return Inertia::render('Admin/Visitantes/Form', [
            'visitante' => $visitante->only(
                'id', 'name', 'telefone',
                'como_conheceu', 'convidado_por_id',
                'primeira_visita', 'observacoes_pastorais',
                'batizado_aguas', 'familia_id',
            ),
            'convidadores' => $this->convidadoresOptions($visitante->id),
            'familias'     => $this->familiasOptions(),
        ]);
    }

    public function update(Request $request, User $visitante)
    {
        abort_unless($visitante->tipo === 'visitante', 404);

        $data = $this->validateData($request);
        $visitante->update($data);

        return redirect()->route('admin.visitantes.index')
            ->with('success', 'Visitante atualizado!');
    }

    public function destroy(User $visitante)
    {
        abort_unless($visitante->tipo === 'visitante', 404);

        $visitante->delete();

        return redirect()->route('admin.visitantes.index')
            ->with('success', 'Visitante removido!');
    }

    public function promoverParaMembro(User $visitante)
    {
        abort_unless($visitante->tipo === 'visitante', 404);

        $visitante->update(['tipo' => 'membro']);

        return redirect()->route('admin.membros.edit', $visitante)
            ->with('success', "{$visitante->name} foi promovido a membro. Complete o cadastro pastoral.");
    }

    private function validateData(Request $request): array
    {
        $request->merge(['cpf' => Cpf::normalize($request->input('cpf'))]);

        return $request->validate([
            'name'                  => 'required|string|max:100',
            'telefone'              => 'required|string|max:20',
            'cpf'                   => 'nullable|string|max:14|unique:users,cpf',
            'como_conheceu'         => 'nullable|string|max:255',
            'convidado_por_id'      => 'nullable|exists:users,id',
            'primeira_visita'       => 'nullable|date|before_or_equal:today',
            'observacoes_pastorais' => 'nullable|string|max:2000',
            'batizado_aguas'        => 'nullable|boolean',
            'familia_id'            => 'nullable|exists:familias,id',
        ]);
    }

    private function convidadoresOptions(?int $excludeId = null): array
    {
        return User::where('tipo', 'membro')
            ->where('is_superadmin', false)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($u) => ['id' => $u->id, 'name' => $u->name])
            ->all();
    }

    private function familiasOptions(): array
    {
        return Familia::with('responsavel:id,name')
            ->get(['id', 'responsavel_id'])
            ->map(fn($f) => ['id' => $f->id, 'nome' => $f->nome])
            ->sortBy('nome')
            ->values()
            ->all();
    }
}
