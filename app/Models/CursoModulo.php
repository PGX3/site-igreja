<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CursoModulo extends Model
{
    protected $table = 'curso_modulos';

    protected $fillable = ['curso_id', 'titulo', 'descricao', 'ordem'];

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function aulas()
    {
        return $this->hasMany(Aula::class, 'modulo_id')->orderBy('ordem');
    }
}
