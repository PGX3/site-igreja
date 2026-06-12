<?php

namespace App\Http\Requests\Api;

use App\Models\Escala;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEscalaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user   = $this->user();
        $escala = $this->route('escala');

        if (! $user || ! $escala instanceof Escala) {
            return false;
        }

        $grupoAlvo = (int) ($this->input('grupo_id') ?? $escala->grupo_id);

        return $user->canManageGrupo($escala->grupo_id)
            && $user->canManageGrupo($grupoAlvo);
    }

    public function rules(): array
    {
        return [
            'titulo'      => ['sometimes', 'required', 'string', 'max:150'],
            'descricao'   => ['nullable', 'string'],
            'data'        => ['sometimes', 'required', 'date'],
            'hora_inicio' => ['sometimes', 'required', 'string'],
            'hora_fim'    => ['sometimes', 'required', 'string'],
            'grupo_id'    => ['sometimes', 'required', 'integer', 'exists:grupos,id'],
            'culto_id'    => ['nullable', 'integer', 'exists:cultos,id', 'prohibits:evento_id'],
            'evento_id'   => ['nullable', 'integer', 'exists:eventos,id'],
            'status'      => ['sometimes', 'required', Rule::in(['pendente', 'confirmada', 'em_andamento', 'concluida', 'cancelada'])],
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
            'status'      => 'status',
        ];
    }
}
