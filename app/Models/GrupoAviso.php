<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoAviso extends Model
{
    protected $fillable = ['grupo_id', 'autor_id', 'titulo', 'corpo', 'fixado'];

    protected $casts = ['fixado' => 'boolean'];

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }

    public function autor()
    {
        return $this->belongsTo(User::class, 'autor_id');
    }
}
