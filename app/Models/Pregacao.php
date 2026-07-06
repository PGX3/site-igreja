<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pregacao extends Model
{
    use HasFactory;

    protected $table = 'pregacoes';

    protected $fillable = ['titulo', 'pregador', 'versiculo', 'youtube_url', 'descricao', 'data_pregacao', 'ativo'];

    protected $casts = [
        'data_pregacao' => 'date',
        'ativo' => 'boolean',
    ];
}
