<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pregação. Nasce como rascunho (roteiro do pregador, lido no iPad do púlpito)
 * e aparece no site quando tem o link do YouTube e está ativa.
 */
class Pregacao extends Model
{
    use HasFactory;

    public const ROTEIRO_FONTE_MIN = 16;

    public const ROTEIRO_FONTE_MAX = 48;

    protected $table = 'pregacoes';

    // pregador_id fica fora: é definido pelo controller (pastor escolhe; pregador = ele mesmo).
    protected $fillable = ['titulo', 'pregador', 'versiculo', 'youtube_url', 'descricao', 'data_pregacao', 'ativo', 'roteiro', 'roteiro_fonte'];

    protected $casts = [
        'data_pregacao' => 'date',
        'ativo' => 'boolean',
        'roteiro_fonte' => 'integer',
    ];

    /** O que aparece no site, no app e no MCP: ativa e com vídeo. */
    public function scopePublicadas(Builder $query): Builder
    {
        return $query->where('ativo', true)->whereNotNull('youtube_url')->where('youtube_url', '!=', '');
    }

    public function publicada(): bool
    {
        return $this->ativo && filled($this->youtube_url);
    }

    public function pregadorUsuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pregador_id');
    }

    /** Pastor edita todas; o pregador, as suas. */
    public function podeSerEditadaPor(User $user): bool
    {
        return $user->isPastor() || ($this->pregador_id !== null && $this->pregador_id === $user->id);
    }
}
