<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sugestao extends Model
{
    protected $table = 'sugestoes';

    protected $fillable = ['nome', 'email', 'mensagem', 'lida'];
}
