<?php

namespace App\Services;

use App\Models\User;
use App\Support\Telefone;

/**
 * Regras comuns do cadastro de visitante (formulário /sou-novo e iPad da recepção).
 */
class RegistroVisitante
{
    /** Pessoa já cadastrada com o mesmo número (ignora formatação e DDI). */
    public function porTelefone(?string $telefone): ?User
    {
        $sufixo = Telefone::sufixo($telefone);

        if ($sufixo === null) {
            return null;
        }

        return User::whereNotNull('telefone')
            ->where('telefone', 'like', '%'.$sufixo.'%')
            ->get()
            ->first(fn (User $u) => Telefone::mesmoNumero($u->telefone, $telefone));
    }
}
