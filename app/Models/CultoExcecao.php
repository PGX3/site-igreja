<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CultoExcecao extends Model
{
    protected $table = 'culto_excecoes';

    protected $fillable = ['culto_id', 'data', 'nova_data', 'novo_horario', 'cancelado', 'observacao'];

    protected $casts = [
        'data' => 'date',
        'nova_data' => 'date',
        'cancelado' => 'boolean',
    ];

    public function culto(): BelongsTo
    {
        return $this->belongsTo(Culto::class);
    }
}
