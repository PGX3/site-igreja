<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Aula extends Model
{
    protected $table = 'aulas';

    public const TIPOS = ['aula', 'material', 'atividade'];

    protected $fillable = [
        'modulo_id', 'titulo', 'tipo', 'conteudo', 'youtube_url', 'data_entrega', 'pontos', 'share_token', 'ordem', 'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'data_entrega' => 'date',
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

    public function anexos()
    {
        return $this->hasMany(AulaAnexo::class)->orderBy('ordem')->orderBy('id');
    }

    public function comentarios()
    {
        return $this->hasMany(AulaComentario::class)->latest();
    }

    public function paraLeitura(): array
    {
        $this->loadMissing('anexos');

        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'tipo' => $this->tipo,
            'conteudo' => $this->conteudo,
            'youtube_url' => $this->youtube_url,
            'data_entrega' => $this->data_entrega?->format('d/m/Y'),
            'pontos' => $this->pontos,
            'anexos' => $this->anexos->map(fn ($a) => $a->paraLeitura())->values(),
        ];
    }

    public function resumo(int $limite = 110): string
    {
        return Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $this->conteudo))), $limite);
    }
}
