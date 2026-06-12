<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class MinhaSenhaController extends Controller
{
    public function edit()
    {
        return Inertia::render('Admin/MinhaSenha');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'senha_atual'            => 'required|string',
            'nova_senha'             => 'required|string|min:6|confirmed',
            'nova_senha_confirmation' => 'required|string',
        ]);

        $user = auth()->user();

        if (!Hash::check($data['senha_atual'], $user->password)) {
            return back()->withErrors(['senha_atual' => 'Senha atual incorreta.']);
        }

        $user->update(['password' => Hash::make($data['nova_senha'])]);

        return back()->with('success', 'Senha alterada com sucesso!');
    }
}
