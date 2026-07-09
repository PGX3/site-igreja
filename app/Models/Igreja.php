<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Igreja extends Model
{
    protected $table = 'igreja';

    protected $fillable = ['nome', 'cnpj', 'endereco', 'cidade', 'telefone', 'email', 'site', 'logo_path'];

    /**
     * Retorna a linha única de configuração da igreja, criando-a com os
     * valores padrão caso ainda não exista (tabela singleton).
     */
    public static function atual(): self
    {
        return static::firstOrCreate([], [
            'nome' => config('app.name', 'Igreja'),
        ]);
    }
}
