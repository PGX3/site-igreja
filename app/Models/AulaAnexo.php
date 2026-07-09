<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AulaAnexo extends Model
{
    protected $table = 'aula_anexos';

    protected $fillable = ['aula_id', 'tipo', 'titulo', 'arquivo_path', 'arquivo_nome', 'url', 'ordem'];

    public function aula()
    {
        return $this->belongsTo(Aula::class);
    }

    /**
     * URL para abrir/baixar o anexo (arquivo no storage público ou link externo).
     */
    public function link(): ?string
    {
        return $this->tipo === 'arquivo'
            ? ($this->arquivo_path ? Storage::url($this->arquivo_path) : null)
            : $this->url;
    }

    public function rotulo(): string
    {
        return $this->titulo ?: ($this->arquivo_nome ?: $this->url ?: 'Anexo');
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
