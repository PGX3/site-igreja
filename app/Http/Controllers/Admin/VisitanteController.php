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
                'email'           => $u->email,
                'telefone'        => $u->telefone,
                'data_nascimento' => optional($u->data_nascimento)->format('Y-m-d'),
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
        if (! request()->user()?->isPastor()) abort(403);

        return Inertia::render('Admin/Visitantes/Form', [
            'convidadores' => $this->convidadoresOptions(),
            'familias'     => $this->familiasOptions(),
        ]);
    }

    public function store(Request $request)
    {
        if (! request()->user()?->isPastor()) abort(403);

        $data = $this->validateData($request);
        $data['tipo'] = 'visitante';

        User::create($data);

        return redirect()->route('admin.visitantes.index')
            ->with('success', 'Visitante cadastrado!');
    }

    public function show(User $visitante)
    {
        abort_unless($visitante->tipo === 'visitante', 404);

        return Inertia::render('Admin/Visitantes/Form', [
            'visitante'    => array_merge(
                $visitante->only(
                    'id', 'name', 'email', 'telefone',
                    'como_conheceu', 'convidado_por_id',
                    'primeira_visita', 'observacoes_pastorais',
                    'batizado_aguas', 'familia_id',
                ),
                ['data_nascimento' => optional($visitante->data_nascimento)->format('Y-m-d')],
            ),
            'convidadores' => $this->convidadoresOptions($visitante->id),
            'familias'     => $this->familiasOptions(),
            'readonly'     => true,
        ]);
    }

    public function edit(User $visitante)
    {
        abort_unless($visitante->tipo === 'visitante', 404);
        if (! request()->user()?->isPastor()) abort(403);

        return Inertia::render('Admin/Visitantes/Form', [
            'visitante' => array_merge(
                $visitante->only(
                    'id', 'name', 'email', 'telefone',
                    'como_conheceu', 'convidado_por_id',
                    'primeira_visita', 'observacoes_pastorais',
                    'batizado_aguas', 'familia_id',
                ),
                ['data_nascimento' => optional($visitante->data_nascimento)->format('Y-m-d')],
            ),
            'convidadores' => $this->convidadoresOptions($visitante->id),
            'familias'     => $this->familiasOptions(),
        ]);
    }

    public function update(Request $request, User $visitante)
    {
        abort_unless($visitante->tipo === 'visitante', 404);

        $data = $this->validateData($request, $visitante->id);
        $visitante->update($data);

        return redirect()->route('admin.visitantes.index')
            ->with('success', 'Visitante atualizado!');
    }

    public function destroy(User $visitante)
    {
        abort_unless($visitante->tipo === 'visitante', 404);
        if (! request()->user()?->isPastor()) abort(403);

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

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $request->merge(['cpf' => Cpf::normalize($request->input('cpf'))]);

        $emailRule = 'nullable|email|max:191';
        $emailRule .= $ignoreId ? '|unique:users,email,' . $ignoreId : '|unique:users,email';

        $cpfRule = 'nullable|string|max:14';
        $cpfRule .= $ignoreId ? '|unique:users,cpf,' . $ignoreId : '|unique:users,cpf';

        return $request->validate([
            'name'                  => 'required|string|max:100',
            'email'                 => $emailRule,
            'telefone'              => 'required|string|max:20',
            'data_nascimento'       => 'required|date|before:today',
            'cpf'                   => $cpfRule,
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
