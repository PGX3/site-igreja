<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    use HasFactory;

    protected $fillable = ['nome', 'data_evento', 'horario', 'local', 'descricao', 'ativo'];

    protected $casts = [
        'data_evento' => 'date',
        'ativo' => 'boolean',
    ];
}
