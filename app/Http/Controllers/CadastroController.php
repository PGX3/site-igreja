<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CadastroController extends Controller
{
    public function create()
    {
        return Inertia::render('Cadastro/Form');
    }

    public function store(Request $request)
    {
        if ($request->filled('_honey')) {
            return redirect()->route('cadastro.obrigado');
        }

        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'telefone'        => 'required|string|max:20',
            'data_nascimento' => 'nullable|date|before:today',
            'sexo'            => 'nullable|in:M,F',
            'estado_civil'    => 'nullable|string|max:30',
            'cpf'             => 'nullable|string|max:14',
            'endereco'        => 'nullable|string|max:255',
            'cidade'          => 'nullable|string|max:80',
            'uf'              => 'nullable|string|size:2',
            'cep'             => 'nullable|string|max:10',
            'como_conheceu'   => 'nullable|string|max:255',
            'primeira_visita' => 'nullable|date|before_or_equal:today',
            'tipo'            => 'required|in:membro,visitante',
            'batizado_aguas'  => 'nullable|boolean',
        ]);

        User::create($data);

        return redirect()->route('cadastro.obrigado');
    }

    public function obrigado()
    {
        return Inertia::render('Cadastro/Obrigado');
    }
}
