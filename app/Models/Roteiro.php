<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Roteiro (rascunho) de uma pregação. O pregador escreve e formata no painel;
 * o app Pregação do iPad do púlpito lê pela API `/api/v1/pulpito`.
 */
class Roteiro extends Model
{
    public const TAMANHO_FONTE_MIN = 16;

    public const TAMANHO_FONTE_MAX = 48;

    protected $fillable = ['titulo', 'data', 'versiculo', 'conteudo', 'tamanho_fonte'];

    protected $casts = [
        'data' => 'date',
        'tamanho_fonte' => 'integer',
    ];

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Dono do roteiro ou pastor. */
    public function podeSerEditadoPor(User $user): bool
    {
        return $user->isPastor() || $this->user_id === $user->id;
    }
}
