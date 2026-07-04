<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'telefone' => $this->telefone,
            'data_nascimento' => $this->data_nascimento?->format('Y-m-d'),
            'sexo' => $this->sexo,
            'estado_civil' => $this->estado_civil,
            'tipo' => $this->tipo,
            'batizado_aguas' => (bool) $this->batizado_aguas,
            'disponibilidade' => $this->disponibilidade,
            'role' => $this->whenLoaded('role', fn () => [
                'id' => $this->role->id,
                'name' => $this->role->name,
            ]),
            'familia_id' => $this->familia_id,
        ];
    }
}
