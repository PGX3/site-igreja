<?php

namespace App\Services;

use App\Models\EscalaMembro;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class EscalaConfirmacaoService
{
    public function confirmar(EscalaMembro $em, User $actor): EscalaMembro
    {
        $this->assertOwner($em, $actor);

        $em->update([
            'status' => 'confirmado',
            'confirmado_em' => now(),
        ]);

        return $em->fresh();
    }

    public function recusar(EscalaMembro $em, User $actor): EscalaMembro
    {
        $this->assertOwner($em, $actor);

        $em->update(['status' => 'recusado']);

        return $em->fresh();
    }

    protected function assertOwner(EscalaMembro $em, User $actor): void
    {
        if ($em->user_id !== $actor->id) {
            throw new AuthorizationException('Você não tem permissão para alterar esta escala.');
        }
    }
}
