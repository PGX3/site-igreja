<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * iPad do púlpito (app Pregação): lê os roteiros pela API `/api/v1/pulpito`
 * com um token próprio (Bearer), sem login de usuário.
 */
class PulpitoDispositivo extends Model
{
    protected $table = 'pulpito_dispositivos';

    protected $fillable = ['nome', 'created_by'];

    protected $hidden = ['token_hash'];

    protected $casts = [
        'ultimo_uso_em' => 'datetime',
    ];

    /** Cria o aparelho e devolve [dispositivo, token em texto puro]. */
    public static function criar(string $nome, ?int $criadoPor = null): array
    {
        $token = 'plp_'.Str::random(40);

        $dispositivo = new self(['nome' => $nome, 'created_by' => $criadoPor]);
        $dispositivo->token_hash = hash('sha256', $token);
        $dispositivo->save();

        return [$dispositivo, $token];
    }

    public static function porToken(?string $token): ?self
    {
        if ($token === null || $token === '') {
            return null;
        }

        return self::where('token_hash', hash('sha256', $token))->first();
    }
}
