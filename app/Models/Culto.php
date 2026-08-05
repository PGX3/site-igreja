<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Culto extends Model
{
    use HasFactory;

    protected $fillable = ['nome', 'dia_semana', 'horario', 'hora_inicio', 'hora_fim', 'descricao', 'ativo'];

    protected $casts = ['ativo' => 'boolean'];
}
