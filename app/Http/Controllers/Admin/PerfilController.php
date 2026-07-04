<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PerfilController extends Controller
{
    public function edit()
    {
        $user = auth()->user();

        return Inertia::render('Admin/Perfil', [
            'perfil' => [
                'name' => $user->name,
                'email' => $user->email,
                'telefone' => $user->telefone,
                'data_nascimento' => $user->data_nascimento?->format('Y-m-d'),
                'callmebot_apikey' => $user->callmebot_apikey,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'telefone' => 'nullable|string|max:30',
            'data_nascimento' => 'nullable|date',
            'callmebot_apikey' => 'nullable|string|max:100',
        ], [], [
            'name' => 'nome',
            'data_nascimento' => 'data de nascimento',
        ]);

        $user->update($data);

        return back()->with('success', 'Perfil atualizado!');
    }
}
