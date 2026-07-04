<?php

namespace App\Http\Requests\Api;

use App\Support\Cpf;
use Illuminate\Foundation\Http\FormRequest;

class StoreCadastroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cpf' => Cpf::normalize($this->input('cpf')),
            'uf' => $this->input('uf') ? strtoupper($this->input('uf')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:191', 'unique:users,email'],
            'telefone' => ['required', 'string', 'max:20'],
            'data_nascimento' => ['required', 'date', 'before:today'],
            'sexo' => ['nullable', 'in:M,F'],
            'estado_civil' => ['nullable', 'string', 'max:30'],
            'cpf' => ['nullable', 'string', 'max:14', 'unique:users,cpf'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:80'],
            'uf' => ['nullable', 'string', 'size:2'],
            'cep' => ['nullable', 'string', 'max:10'],
            'como_conheceu' => ['nullable', 'string', 'max:255'],
            'primeira_visita' => ['nullable', 'date', 'before_or_equal:today'],
            'tipo' => ['required', 'in:membro,visitante'],
            'batizado_aguas' => ['required', 'boolean'],
        ];
    }
}
