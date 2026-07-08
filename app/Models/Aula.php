<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Aula extends Model
{
    protected $table = 'aulas';

    public const TIPOS = ['aula', 'material', 'atividade'];

    protected $fillable = [
        'modulo_id', 'titulo', 'tipo', 'conteudo', 'youtube_url', 'share_token', 'ordem', 'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function gerarShareToken(): string
    {
        if (! $this->share_token) {
            $this->update(['share_token' => Str::random(40)]);
        }

        return $this->share_token;
    }

    public function revogarLink(): void
    {
        $this->update(['share_token' => null]);
    }

    public function modulo()
    {
        return $this->belongsTo(CursoModulo::class, 'modulo_id');
    }

    public function paraLeitura(): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'tipo' => $this->tipo,
            'conteudo' => $this->conteudo,
            'youtube_url' => $this->youtube_url,
        ];
    }

    public function resumo(int $limite = 110): string
    {
        return Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $this->conteudo))), $limite);
    }
}
