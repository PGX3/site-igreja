<?php

namespace App\Events;

use App\Models\Escala;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MembroAdicionadoEscala
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public Escala $escala,
        public ?string $funcao = null,
    ) {}
}
