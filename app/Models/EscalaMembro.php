<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EscalaMembro extends Model
{
    protected $table = 'escala_membros';

    protected $fillable = [
        'escala_id', 'user_id', 'funcao', 'status', 'observacao', 'confirmado_em',
    ];

    protected $casts = [
        'confirmado_em' => 'datetime',
    ];

    public function escala()
    {
        return $this->belongsTo(Escala::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
