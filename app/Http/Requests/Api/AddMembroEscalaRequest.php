<?php

namespace App\Http\Requests\Api;

use App\Models\Escala;
use Illuminate\Foundation\Http\FormRequest;

class AddMembroEscalaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $escala = $this->route('escala');

        if (! $user || ! $escala instanceof Escala) {
            return false;
        }

        return $user->canManageGrupo($escala->grupo_id);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'funcao' => ['nullable', 'string', 'max:100'],
        ];
    }
}
