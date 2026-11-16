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

    /** Quem do grupo faz esta função (base da sugestão de escala). */
    public function membros()
    {
        return $this->belongsToMany(User::class, 'grupo_funcao_user')->withTimestamps();
    }
}
