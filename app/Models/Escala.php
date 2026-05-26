<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model; 

class Escala extends Model
{
    protected $fillable = ['grupo_id', 'culto_id', 'data', 'created_by'];
    
    protected $casts = [
        'grupo_id' => 'integer',
        'culto_id' => 'integer',
        'created_by' => 'integer',
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
    
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}