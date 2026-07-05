<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EscalaSetlistItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ordem' => $this->ordem,
            'tom' => $this->tom,
            'observacao' => $this->observacao,
            'musica' => $this->whenLoaded('musica', fn () => [
                'id' => $this->musica->id,
                'nome' => $this->musica->nome,
                'tom' => $this->musica->tom,
                'letra' => $this->musica->letra,
                'link' => $this->musica->link,
            ]),
        ];
    }
}
