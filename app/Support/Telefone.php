<?php

namespace App\Support;

class Telefone
{
    /**
     * Reduz o telefone aos dígitos. O valor guardado em `users.telefone`
     * continua formatado como o usuário digitou.
     */
    public static function normalize(?string $telefone): ?string
    {
        if ($telefone === null) {
            return null;
        }
        $digitos = preg_replace('/\D/', '', $telefone);

        return $digitos === '' ? null : $digitos;
    }

    /**
     * Chave de comparação entre dois telefones: os últimos 11 dígitos,
     * para que "+55 51 99282-4071" e "(51) 99282-4071" sejam o mesmo número.
     */
    public static function chave(?string $telefone): ?string
    {
        $digitos = self::normalize($telefone);

        return $digitos === null ? null : substr($digitos, -11);
    }

    /**
     * Últimos 4 dígitos, usados para pré-filtrar candidatos no banco
     * (portável entre SQLite e MySQL) antes da comparação exata em PHP.
     */
    public static function sufixo(?string $telefone): ?string
    {
        $digitos = self::normalize($telefone);

        return $digitos === null ? null : substr($digitos, -4);
    }

    public static function mesmoNumero(?string $a, ?string $b): bool
    {
        $chaveA = self::chave($a);

        return $chaveA !== null && $chaveA === self::chave($b);
    }
}
