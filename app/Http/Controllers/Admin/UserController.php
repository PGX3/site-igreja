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
        $usuarios = User::with(['role', 'grupo'])
            ->orderBy('name')
            ->get()
            ->map(fn($u) => [
                'id'     => $u->id,
                'name'   => $u->name,
                'email'  => $u->email,
                'role'   => $u->role?->only('name', 'display_name'),
                'grupo'  => $u->grupo?->only('id', 'nome'),
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
            'grupo_id'         => 'nullable|exists:grupos,id',
            'telefone'         => 'nullable|string|max:20',
            'callmebot_apikey' => 'nullable|string|max:20',
        ]);

        $data['password'] = Hash::make($data['password']);
        User::create($data);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuário criado!');
    }

    public function edit(User $usuario)
    {
        return Inertia::render('Admin/Usuarios/Form', [
            'usuario' => $usuario->only('id', 'name', 'email', 'role_id', 'grupo_id', 'telefone', 'callmebot_apikey'),
            'roles'   => Role::all(['id', 'name', 'display_name']),
            'grupos'  => Grupo::orderBy('nome')->get(['id', 'nome']),
        ]);
    }

    public function update(Request $request, User $usuario)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'email'            => 'required|email|unique:users,email,' . $usuario->id,
            'password'         => 'nullable|string|min:6',
            'role_id'          => 'required|exists:roles,id',
            'grupo_id'         => 'nullable|exists:grupos,id',
            'telefone'         => 'nullable|string|max:20',
            'callmebot_apikey' => 'nullable|string|max:20',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $usuario->update($data);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuário atualizado!');
    }

    public function destroy(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'Você não pode excluir seu próprio usuário.');
        }

        $usuario->delete();
        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuário excluído!');
    }
}
