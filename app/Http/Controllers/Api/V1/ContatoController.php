<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePedidoOracaoRequest;
use App\Http\Requests\Api\StoreSugestaoRequest;
use App\Services\ContatoService;
use Illuminate\Http\JsonResponse;

class ContatoController extends Controller
{
    public function __construct(private ContatoService $contatos) {}

    public function sugestao(StoreSugestaoRequest $request): JsonResponse
    {
        $sugestao = $this->contatos->criarSugestao($request->validated());

        return response()->json([
            'message' => 'Sugestão enviada. Obrigado!',
            'id'      => $sugestao->id,
        ], 201);
    }

    public function pedidoOracao(StorePedidoOracaoRequest $request): JsonResponse
    {
        $pedido = $this->contatos->criarPedidoOracao($request->validated());

        return response()->json([
            'message' => 'Seu pedido foi recebido. Estaremos orando por você.',
            'id'      => $pedido->id,
        ], 201);
    }
}
