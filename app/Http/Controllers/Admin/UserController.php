<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grupo;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::with(['role', 'grupos'])
            ->where('is_superadmin', false)
            ->orderBy('name')
            ->get()
            ->map(fn($u) => [
                'id'       => $u->id,
                'name'     => $u->name,
                'email'    => $u->email,
                'role'     => $u->role?->only('name', 'display_name'),
                'grupos'   => $u->grupos->map->only('id', 'nome')->values(),
                'telefone' => $u->telefone,
            ]);

        return Inertia::render('Admin/Usuarios/Index', compact('usuarios'));
    }

    public function create()
    {
        return Inertia::render('Admin/Usuarios/Form', [
            'roles'  => Role::all(['id', 'name', 'display_name']),
            'grupos' => Grupo::orderBy('nome')->get(['id', 'nome']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'email'            => 'required|email|unique:users',
            'password'         => 'required|string|min:6',
            'role_id'          => 'required|exists:roles,id',
            'grupo_ids'        => 'nullable|array',
            'grupo_ids.*'      => 'exists:grupos,id',
            'telefone'         => 'nullable|string|max:20',
            'data_nascimento'  => 'nullable|date|before:today',
        ]);

        $grupoIds = $data['grupo_ids'] ?? [];
        unset($data['grupo_ids']);
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        $user->grupos()->sync($grupoIds);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuário criado!');
    }

    public function edit(User $usuario)
    {
        if ($usuario->is_superadmin) abort(403);

        return Inertia::render('Admin/Usuarios/Form', [
            'usuario' => array_merge(
                $usuario->only('id', 'name', 'email', 'role_id', 'telefone'),
                [
                    'data_nascimento' => $usuario->data_nascimento?->format('Y-m-d'),
                    'grupo_ids'       => $usuario->grupos()->pluck('grupos.id')->map(fn($id) => (int) $id)->toArray(),
                ]
            ),
            'roles'  => Role::all(['id', 'name', 'display_name']),
            'grupos' => Grupo::orderBy('nome')->get(['id', 'nome']),
        ]);
    }

    public function update(Request $request, User $usuario)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'email'            => 'required|email|unique:users,email,' . $usuario->id,
            'password'         => 'nullable|string|min:6',
            'role_id'          => 'required|exists:roles,id',
            'grupo_ids'        => 'nullable|array',
            'grupo_ids.*'      => 'exists:grupos,id',
            'telefone'         => 'nullable|string|max:20',
            'data_nascimento'  => 'nullable|date|before:today',
        ]);

        $grupoIds = $data['grupo_ids'] ?? [];
        unset($data['grupo_ids']);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $usuario->update($data);
        $usuario->grupos()->sync($grupoIds);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuário atualizado!');
    }

    public function alterarSenha(Request $request, User $usuario)
    {
        if ($usuario->is_superadmin) abort(403);

        $data = $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $usuario->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', "Senha de {$usuario->name} alterada com sucesso!");
    }

    public function destroy(User $usuario)
    {
        if ($usuario->is_superadmin) abort(403);

        if ($usuario->id === auth()->user()?->id) {
            return back()->with('error', 'Você não pode excluir seu próprio usuário.');
        }

        $usuario->delete();
        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuário excluído!');
    }
}
