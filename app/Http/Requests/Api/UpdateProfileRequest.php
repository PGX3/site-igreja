<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['sometimes', 'string', 'max:150'],
            'email' => ['sometimes', 'email', 'max:150', Rule::unique('users', 'email')->ignore($userId)],
            'telefone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'data_nascimento' => ['sometimes', 'nullable', 'date'],
            'sexo' => ['sometimes', 'nullable', Rule::in(['M', 'F'])],
            'estado_civil' => ['sometimes', 'nullable', 'string', 'max:30'],
        ];
    }
}
