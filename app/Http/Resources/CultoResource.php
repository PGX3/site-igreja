<?php

namespace App\Http\Resources;

use App\Services\CultoProximaDataResolver;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CultoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $proxima = app(CultoProximaDataResolver::class)->resolve($this->resource);

        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'dia_semana' => $this->dia_semana,
            'horario' => $this->horario ? substr((string) $this->horario, 0, 5) : null,
            'descricao' => $this->descricao,
            'ativo' => (bool) $this->ativo,
            'proxima_data' => $proxima->format('Y-m-d'),
        ];
    }
}
