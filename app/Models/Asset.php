<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = ['tipo', 'titulo', 'arquivo_path', 'arquivo_nome', 'created_by'];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function escalas()
    {
        return $this->belongsToMany(Escala::class, 'asset_escala')->withTimestamps();
    }
}
