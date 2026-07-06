<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StorePedidoOracaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'min:2', 'max:100'],
            'pedido' => ['required', 'string', 'min:10', 'max:2000'],
            'anonimo' => ['sometimes', 'boolean'],
            'compartilhar' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome é obrigatório.',
            'nome.min' => 'O nome deve ter ao menos 2 caracteres.',
            'pedido.required' => 'O pedido é obrigatório.',
            'pedido.min' => 'Descreva melhor seu pedido (mínimo 10 caracteres).',
        ];
    }
}
