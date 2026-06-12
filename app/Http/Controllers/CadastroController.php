<?php

namespace App\Http\Controllers;

use App\Models\Familia;
use App\Models\User;
use App\Support\Cpf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $request->merge(['cpf' => Cpf::normalize($request->input('cpf'))]);

        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'email'           => 'nullable|email|max:191|unique:users,email',
            'telefone'        => 'required|string|max:20',
            'data_nascimento' => 'required|date|before:today',
            'sexo'            => 'nullable|in:M,F',
            'estado_civil'    => 'nullable|string|max:30',
            'cpf'             => 'nullable|string|max:14|unique:users,cpf',
            'endereco'        => 'nullable|string|max:255',
            'cidade'          => 'nullable|string|max:80',
            'uf'              => 'nullable|string|size:2',
            'cep'             => 'nullable|string|max:10',
            'como_conheceu'   => 'nullable|string|max:255',
            'primeira_visita' => 'nullable|date|before_or_equal:today',
            'tipo'            => 'required|in:membro,visitante',
            'batizado_aguas'  => 'required|boolean',
        ]);

        DB::transaction(function () use ($data) {
            $familiaId = null;

            if (!empty($data['endereco']) || !empty($data['cidade']) || !empty($data['cep'])) {
                $familia = Familia::create([
                    'endereco'           => $data['endereco'] ?? '',
                    'cidade'             => $data['cidade'] ?? '',
                    'uf'                 => strtoupper($data['uf'] ?? ''),
                    'cep'                => $data['cep'] ?? '',
                    'telefone_principal' => $data['telefone'] ?? null,
                ]);
                $familiaId = $familia->id;
            }

            $user = User::create([
                'name'            => $data['name'],
                'email'           => $data['email'] ?? null,
                'telefone'        => $data['telefone'],
                'data_nascimento' => $data['data_nascimento'] ?? null,
                'sexo'            => $data['sexo'] ?? null,
                'estado_civil'    => $data['estado_civil'] ?? null,
                'cpf'             => $data['cpf'] ?? null,
                'como_conheceu'   => $data['como_conheceu'] ?? null,
                'primeira_visita' => $data['primeira_visita'] ?? null,
                'tipo'            => $data['tipo'],
                'batizado_aguas'  => $data['batizado_aguas'] ?? null,
                'familia_id'      => $familiaId,
            ]);

            if ($familiaId) {
                Familia::where('id', $familiaId)->update(['responsavel_id' => $user->id]);
            }
        });

        return redirect()->route('cadastro.obrigado');
    }

    public function obrigado()
    {
        return Inertia::render('Cadastro/Obrigado');
    }
}
