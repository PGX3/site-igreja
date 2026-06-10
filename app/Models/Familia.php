<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Familia extends Model
{
    use HasFactory;

    protected $table = 'familias';

    protected $fillable = [
        'endereco', 'cidade', 'uf', 'cep',
        'telefone_principal', 'responsavel_id', 'observacoes',
    ];

    protected $appends = ['nome'];

    public function responsavel()
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function membros()
    {
        return $this->hasMany(User::class, 'familia_id');
    }

    public function getNomeAttribute(): string
    {
        if ($this->relationLoaded('responsavel') && $this->responsavel) {
            return 'Família de ' . $this->responsavel->name;
        }

        if ($this->responsavel_id) {
            $r = User::find($this->responsavel_id);
            if ($r) return 'Família de ' . $r->name;
        }

        if ($this->relationLoaded('membros') && $this->membros->isNotEmpty()) {
            return 'Família de ' . $this->membros->first()->name;
        }

        return 'Família sem responsável';
    }

    public function enderecoCompleto(): string
    {
        $partes = array_filter([
            $this->endereco,
            $this->cidade,
            $this->uf,
            $this->cep,
        ]);
        return implode(', ', $partes);
    }
}
