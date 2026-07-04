<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EscalaSetlistItem extends Model
{
    protected $table = 'escala_setlist_itens';

    protected $fillable = ['escala_id', 'musica_id', 'tom', 'ordem', 'observacao'];

    public function escala()
    {
        return $this->belongsTo(Escala::class);
    }

    public function musica()
    {
        return $this->belongsTo(Musica::class);
    }
}
