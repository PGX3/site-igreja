<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    protected $fillable = ['nome', 'descricao', 'tem_musicas', 'whatsapp_apikey', 'whatsapp_phone', 'lider_id', 'created_by'];

    protected $casts = [
        'lider_id' => 'integer',
        'created_by' => 'integer',
        'tem_musicas' => 'boolean',
    ];

    public function lider()
    {
        return $this->belongsTo(User::class, 'lider_id');
    }

    public function membros()
    {
        return $this->belongsToMany(User::class);
    }

    public function escalas()
    {
        return $this->hasMany(Escala::class);
    }

    public function funcoes()
    {
        return $this->hasMany(GrupoFuncao::class)->orderBy('nome');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function avisos()
    {
        return $this->hasMany(GrupoAviso::class);
    }
}
