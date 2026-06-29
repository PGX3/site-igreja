<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoMusica extends Model
{
    protected $fillable = ['grupo_id', 'uploaded_by', 'nome', 'tom', 'letra', 'arquivo_path'];

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
