<?php

namespace App\Http\Resources;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Asset */
class EscalaAssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo' => $this->tipo,
            'titulo' => $this->titulo,
            'arquivo_nome' => $this->arquivo_nome,
            'url' => Storage::disk('public')->url($this->arquivo_path),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
