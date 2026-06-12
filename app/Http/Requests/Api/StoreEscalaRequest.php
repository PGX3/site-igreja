<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEscalaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user    = $this->user();
        $grupoId = (int) $this->input('grupo_id');

        if (! $user || ! $grupoId) {
            return false;
        }

        return $user->canManageGrupo($grupoId);
    }

    public function rules(): array
    {
        return [
            'titulo'      => ['required', 'string', 'max:150'],
            'descricao'   => ['nullable', 'string'],
            'data'        => ['required', 'date'],
            'hora_inicio' => ['required', 'string'],
            'hora_fim'    => ['required', 'string'],
            'grupo_id'    => ['required', 'integer', 'exists:grupos,id'],
            'culto_id'    => ['nullable', 'integer', 'exists:cultos,id', 'required_without:evento_id'],
            'evento_id'   => ['nullable', 'integer', 'exists:eventos,id', 'prohibits:culto_id'],
            'membros'              => ['nullable', 'array'],
            'membros.*.user_id'    => ['required', 'integer', 'exists:users,id'],
            'membros.*.funcao'     => ['nullable', 'string', 'max:100'],
        ];
    }

    public function attributes(): array
    {
        return [
            'titulo'      => 'título',
            'data'        => 'data',
            'hora_inicio' => 'horário de início',
            'hora_fim'    => 'horário de fim',
            'grupo_id'    => 'grupo',
            'culto_id'    => 'culto',
            'evento_id'   => 'evento',
        ];
    }
}
