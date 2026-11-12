<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Aparelho da recepção (iPad) que cadastra visitantes pela API
 * `/api/v1/recepcao`. Autentica com um token próprio (Bearer), sem login
 * de usuário; o modo diácono é liberado por um PIN numérico.
 */
class RecepcaoDispositivo extends Model
{
    protected $table = 'recepcao_dispositivos';

    protected $fillable = ['nome', 'created_by'];

    protected $hidden = ['token_hash', 'pin_hash'];

    protected $casts = [
        'ultimo_uso_em' => 'datetime',
        'cadastros' => 'integer',
    ];

    /** Cria o aparelho e devolve [dispositivo, token em texto puro]. */
    public static function criar(string $nome, ?string $pin, ?int $criadoPor = null): array
    {
        $token = 'rcp_'.Str::random(40);

        $dispositivo = new self(['nome' => $nome, 'created_by' => $criadoPor]);
        $dispositivo->token_hash = self::hashToken($token);
        $dispositivo->definirPin($pin);
        $dispositivo->save();

        return [$dispositivo, $token];
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public static function porToken(?string $token): ?self
    {
        if ($token === null || $token === '') {
            return null;
        }

        return self::where('token_hash', self::hashToken($token))->first();
    }

    public function definirPin(?string $pin): void
    {
        $this->pin_hash = filled($pin) ? Hash::make($pin) : null;
    }

    public function temPin(): bool
    {
        return $this->pin_hash !== null;
    }

    public function pinConfere(?string $pin): bool
    {
        return $this->temPin() && filled($pin) && Hash::check((string) $pin, $this->pin_hash);
    }
}
