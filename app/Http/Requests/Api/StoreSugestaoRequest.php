<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreSugestaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome'     => ['required', 'string', 'min:2', 'max:100'],
            'email'    => ['nullable', 'email', 'max:150'],
            'mensagem' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required'     => 'O nome é obrigatório.',
            'nome.min'          => 'O nome deve ter ao menos 2 caracteres.',
            'mensagem.required' => 'A mensagem é obrigatória.',
            'mensagem.min'      => 'A mensagem deve ter ao menos 10 caracteres.',
            'email.email'       => 'Informe um e-mail válido.',
        ];
    }
}
