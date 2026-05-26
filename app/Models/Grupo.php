<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    protected $fillable = ['nome', 'descricao', 'lider_id', 'created_by'];
    
    protected $casts = [
        'lider_id' => 'integer',
        'created_by' => 'integer',
    ];
    
    public function lider()
    {
        return $this->belongsTo(User::class, 'lider_id');
    }
    
    public function membros()
    {
        return $this->hasMany(User::class);
    }
    
    public function escalas()
    {
        return $this->hasMany(Escala::class);
    }
    
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}