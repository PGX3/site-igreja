<?php

namespace App\Support;

class Cpf
{
    public static function normalize(?string $cpf): ?string
    {
        if ($cpf === null) {
            return null;
        }
        $digitos = preg_replace('/\D/', '', $cpf);

        return $digitos === '' ? null : $digitos;
    }

    public static function format(?string $cpf): ?string
    {
        $d = self::normalize($cpf);
        if ($d === null || strlen($d) !== 11) {
            return $d;
        }

        return substr($d, 0, 3).'.'.substr($d, 3, 3).'.'.substr($d, 6, 3).'-'.substr($d, 9, 2);
    }
}
