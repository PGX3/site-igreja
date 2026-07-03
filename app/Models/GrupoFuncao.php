<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoFuncao extends Model
{
    protected $table = 'grupo_funcoes';

    protected $fillable = ['grupo_id', 'nome'];

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }
}
