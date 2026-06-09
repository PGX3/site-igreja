<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MembroController extends Controller
{
    public function index(Request $request)
    {
        $busca = trim((string) $request->query('busca', ''));

        $membros = User::membros()
            ->where('is_superadmin', false)
            ->when($busca !== '', fn($q) =>
                $q->where(fn($w) => $w
                    ->where('name', 'like', "%{$busca}%")
                    ->orWhere('telefone', 'like', "%{$busca}%")
                    ->orWhere('email', 'like', "%{$busca}%")))
            ->with('grupos:id,nome')
            ->orderBy('name')
            ->get()
            ->map(fn($u) => [
                'id'              => $u->id,
                'name'            => $u->name,
                'email'           => $u->email,
                'telefone'        => $u->telefone,
                'cidade'          => $u->cidade,
                'batizado_aguas'  => $u->batizado_aguas,
                'data_nascimento' => optional($u->data_nascimento)->format('Y-m-d'),
                'grupos'          => $u->grupos->map(fn($g) => ['id' => $g->id, 'nome' => $g->nome])->values(),
            ]);

        return Inertia::render('Admin/Membros/Index', [
            'membros' => $membros,
            'busca'   => $busca,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Membros/Form');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['tipo'] = 'membro';

        User::create($data);

        return redirect()->route('admin.membros.index')
            ->with('success', 'Membro cadastrado!');
    }

    public function edit(User $membro)
    {
        abort_unless($membro->tipo === 'membro', 404);
        if ($membro->is_superadmin) abort(403);

        return Inertia::render('Admin/Membros/Form', [
            'membro' => $membro->only(
                'id', 'name', 'email', 'telefone', 'data_nascimento',
                'sexo', 'estado_civil', 'cpf',
                'endereco', 'cidade', 'uf', 'cep',
                'batizado_aguas',
            ),
        ]);
    }

    public function update(Request $request, User $membro)
    {
        abort_unless($membro->tipo === 'membro', 404);
        if ($membro->is_superadmin) abort(403);

        $data = $this->validateData($request, $membro->id);
        $membro->update($data);

        return redirect()->route('admin.membros.index')
            ->with('success', 'Membro atualizado!');
    }

    public function destroy(User $membro)
    {
        abort_unless($membro->tipo === 'membro', 404);
        if ($membro->is_superadmin) abort(403);

        $membro->delete();

        return redirect()->route('admin.membros.index')
            ->with('success', 'Membro removido!');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $emailRule = 'nullable|email';
        $emailRule .= $ignoreId ? '|unique:users,email,' . $ignoreId : '|unique:users,email';

        return $request->validate([
            'name'            => 'required|string|max:100',
            'email'           => $emailRule,
            'telefone'        => 'required|string|max:20',
            'data_nascimento' => 'nullable|date|before:today',
            'sexo'            => 'nullable|in:M,F',
            'estado_civil'    => 'nullable|string|max:30',
            'cpf'             => 'nullable|string|max:14',
            'endereco'        => 'nullable|string|max:255',
            'cidade'          => 'nullable|string|max:80',
            'uf'              => 'nullable|string|size:2',
            'cep'             => 'nullable|string|max:10',
            'batizado_aguas'  => 'nullable|boolean',
        ]);
    }
}
