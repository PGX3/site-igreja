<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AulaAnexo extends Model
{
    protected $table = 'aula_anexos';

    protected $fillable = ['aula_id', 'asset_id', 'tipo', 'titulo', 'arquivo_path', 'arquivo_nome', 'url', 'ordem'];

    public function aula()
    {
        return $this->belongsTo(Aula::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * URL para abrir/baixar o anexo: link externo, arquivo da biblioteca (asset)
     * ou arquivo antigo salvo direto no anexo.
     */
    public function link(): ?string
    {
        if ($this->tipo === 'link') {
            return $this->url;
        }

        if ($this->asset) {
            return Storage::url($this->asset->arquivo_path);
        }

        return $this->arquivo_path ? Storage::url($this->arquivo_path) : null;
    }

    public function rotulo(): string
    {
        return $this->titulo
            ?: $this->asset?->titulo
            ?: $this->asset?->arquivo_nome
            ?: $this->arquivo_nome
            ?: $this->url
            ?: 'Anexo';
    }

    public function paraLeitura(): array
    {
        return [
            'id' => $this->id,
            'tipo' => $this->tipo,
            'titulo' => $this->rotulo(),
            'url' => $this->link(),
        ];
    }
}
