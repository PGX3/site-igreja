<?php

namespace App\Services;

use App\Models\PedidoOracao;
use App\Models\Sugestao;

class ContatoService
{
    public function criarSugestao(array $data): Sugestao
    {
        return Sugestao::create([
            'nome' => $data['nome'],
            'email' => $data['email'] ?? null,
            'mensagem' => $data['mensagem'],
        ]);
    }

    public function criarPedidoOracao(array $data): PedidoOracao
    {
        $anonimo = (bool) ($data['anonimo'] ?? false);

        return PedidoOracao::create([
            'nome' => $anonimo ? 'Anônimo' : $data['nome'],
            'pedido' => $data['pedido'],
            'anonimo' => $anonimo,
        ]);
    }
}
