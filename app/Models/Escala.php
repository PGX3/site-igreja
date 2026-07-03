<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Escala extends Model
{
    protected $fillable = [
        'titulo', 'descricao', 'restricoes', 'data', 'hora_inicio', 'hora_fim',
        'status', 'grupo_id', 'culto_id', 'evento_id',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'data' => 'date',
    ];

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }

    public function culto()
    {
        return $this->belongsTo(Culto::class);
    }

    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function membros()
    {
        return $this->belongsToMany(User::class, 'escala_membros')
            ->withPivot('status', 'funcao', 'observacao', 'confirmado_em')
            ->withTimestamps();
    }

    public function escalaMembros()
    {
        return $this->hasMany(EscalaMembro::class);
    }

    public function assets()
    {
        return $this->belongsToMany(Asset::class, 'asset_escala')->withTimestamps();
    }

    public function notas()
    {
        return $this->hasMany(EscalaNota::class);
    }

    public function setlist()
    {
        return $this->hasMany(EscalaSetlistItem::class)->orderBy('ordem');
    }
}
