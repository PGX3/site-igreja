<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EscalaNota extends Model
{
    protected $table = 'escala_notas';

    protected $fillable = ['escala_id', 'titulo', 'corpo', 'created_by'];

    public function escala()
    {
        return $this->belongsTo(Escala::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
