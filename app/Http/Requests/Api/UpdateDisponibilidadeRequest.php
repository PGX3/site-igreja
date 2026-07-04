<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDisponibilidadeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'disponibilidade' => ['present', 'nullable', 'array'],
            'disponibilidade.*' => ['string', 'max:50'],
        ];
    }
}
