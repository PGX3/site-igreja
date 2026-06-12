<?php

namespace App\Http\Resources;

use App\Models\EscalaMembro;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EscalaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $userId  = $request->user()?->id;
        $pivot   = $this->resource->pivot ?? null;
        $pivotId = null;

        if ($pivot && $userId) {
            $pivotId = EscalaMembro::where('escala_id', $this->id)
                ->where('user_id', $userId)
                ->value('id');
        }

        return [
            'id'             => $this->id,
            'titulo'         => $this->titulo,
            'descricao'      => $this->descricao,
            'data_escala'    => $this->data?->format('Y-m-d'),
            'hora_inicio'    => $this->hora_inicio ? substr((string) $this->hora_inicio, 0, 5) : null,
            'hora_fim'       => $this->hora_fim    ? substr((string) $this->hora_fim,    0, 5) : null,
            'status'         => $this->status,
            'grupo'          => $this->whenLoaded('grupo', fn () => [
                'id'   => $this->grupo->id,
                'nome' => $this->grupo->nome,
            ]),
            'culto'          => $this->whenLoaded('culto', fn () => $this->culto ? [
                'id'   => $this->culto->id,
                'nome' => $this->culto->nome,
            ] : null),
            'evento'         => $this->whenLoaded('evento', fn () => $this->evento ? [
                'id'   => $this->evento->id,
                'nome' => $this->evento->nome,
            ] : null),
            'meu_status'     => $pivot?->status,
            'minha_funcao'   => $pivot?->funcao,
            'confirmado_em'  => $pivot?->confirmado_em
                ? \Carbon\Carbon::parse($pivot->confirmado_em)->toIso8601String()
                : null,
            'pivot_id'       => $pivotId,
        ];
    }
}
