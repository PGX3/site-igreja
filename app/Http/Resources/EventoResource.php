<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->data_evento;

        return [
            'id'          => $this->id,
            'nome'        => $this->nome,
            'data_evento' => $data?->format('Y-m-d'),
            'horario'     => $this->horario ? substr((string) $this->horario, 0, 5) : null,
            'local'       => $this->local,
            'descricao'   => $this->descricao,
            'ativo'       => (bool) $this->ativo,
            'passado'     => $data ? $data->lt(Carbon::today()) : false,
        ];
    }
}
