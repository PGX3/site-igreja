<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoTemplate extends Model
{
    use HasFactory;

    protected $table = 'documento_templates';

    protected $fillable = ['nome', 'descricao', 'corpo', 'variaveis', 'ativo', 'created_by'];

    protected $casts = [
        'variaveis' => 'array',
        'ativo' => 'boolean',
    ];

    public function documentos()
    {
        return $this->hasMany(Documento::class);
    }
}
