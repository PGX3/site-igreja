<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;

    protected $table = 'documentos';

    protected $fillable = ['documento_template_id', 'titulo', 'corpo', 'valores', 'created_by'];

    protected $casts = [
        'valores' => 'array',
    ];

    public function template()
    {
        return $this->belongsTo(DocumentoTemplate::class, 'documento_template_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
