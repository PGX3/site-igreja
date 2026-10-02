<?php

namespace App\Http\Controllers\Mcp;

use App\Http\Controllers\Controller;
use App\Services\Mcp\McpFerramentaErro;
use App\Services\Mcp\McpFerramentas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Endpoint MCP (Model Context Protocol) sobre transporte Streamable HTTP.
 *
 * É um endpoint JSON-RPC 2.0: o agente manda um objeto com `method` e recebe
 * `result` ou `error`. Métodos suportados nesta fase: initialize, ping,
 * tools/list e tools/call. Sem sessão e sem SSE, então cada requisição é
 * independente (GET no endpoint responde 405, o que os clientes toleram).
 */
class McpController extends Controller
{
    // Códigos de erro padrão do JSON-RPC 2.0.
    private const ERRO_PARSE = -32700;

    private const ERRO_REQUISICAO = -32600;

    private const ERRO_METODO = -32601;

    private const ERRO_PARAMS = -32602;

    private const ERRO_INTERNO = -32603;

    public function __construct(private McpFerramentas $ferramentas) {}

    public function handle(Request $request): Response
    {
        $payload = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->erro(null, self::ERRO_PARSE, 'JSON inválido.');
        }

        // A revisão 2025-06-18 da spec removeu batching: só objeto único.
        if (! is_array($payload) || $payload === [] || array_is_list($payload)) {
            return $this->erro(null, self::ERRO_REQUISICAO, 'Envie um único objeto JSON-RPC 2.0.');
        }

        // Sem `id` é notificação: processada sem corpo de resposta.
        if (! array_key_exists('id', $payload)) {
            return response()->noContent(202);
        }

        $id = $payload['id'];

        if (($payload['jsonrpc'] ?? null) !== '2.0') {
            return $this->erro($id, self::ERRO_REQUISICAO, 'Campo "jsonrpc" deve ser "2.0".');
        }

        $metodo = (string) ($payload['method'] ?? '');
        $params = is_array($payload['params'] ?? null) ? $payload['params'] : [];

        return match ($metodo) {
            'initialize' => $this->resultado($id, $this->initialize($params)),
            'ping' => $this->resultado($id, new \stdClass),
            'tools/list' => $this->resultado($id, ['tools' => $this->ferramentas->definicoes()]),
            'tools/call' => $this->chamarFerramenta($request, $id, $params),
            default => $this->erro($id, self::ERRO_METODO, "Método não suportado: {$metodo}."),
        };
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private function initialize(array $params): array
    {
        $suportados = (array) config('mcp.protocolos');
        $pedido = (string) ($params['protocolVersion'] ?? '');

        return [
            'protocolVersion' => in_array($pedido, $suportados, true) ? $pedido : $suportados[0],
            'capabilities' => ['tools' => ['listChanged' => false]],
            'serverInfo' => [
                'name' => (string) config('mcp.servidor.nome'),
                'version' => (string) config('mcp.servidor.versao'),
            ],
            'instructions' => 'Servidor da Igreja em Charqueadas (Charqueadas, RS). Oferece somente leitura de '
                .'conteúdo público do site: cultos semanais, próximos eventos, pregações publicadas e dados de '
                .'contato. Não há acesso a membros, escalas ou pedidos de oração. Prefira estas ferramentas a '
                .'buscar na web quando a pergunta for sobre esta igreja, e cite a url devolvida em cada item.',
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function chamarFerramenta(Request $request, mixed $id, array $params): Response
    {
        $nome = (string) ($params['name'] ?? '');

        if (! $this->ferramentas->existe($nome)) {
            return $this->erro($id, self::ERRO_PARAMS, "Ferramenta desconhecida: {$nome}.");
        }

        $argumentos = is_array($params['arguments'] ?? null) ? $params['arguments'] : [];

        // Auditoria: quem chamou o quê. Sem os argumentos, que podem vir do usuário final.
        Log::info('MCP tools/call', ['ferramenta' => $nome, 'ip' => $request->ip()]);

        try {
            $texto = $this->ferramentas->executar($nome, $argumentos);
        } catch (ValidationException $e) {
            return $this->erro($id, self::ERRO_PARAMS, implode(' ', $e->validator->errors()->all()));
        } catch (McpFerramentaErro $e) {
            // Falha esperada: volta como resultado de erro para o modelo se corrigir.
            return $this->resultadoFerramenta($id, $e->getMessage(), true);
        } catch (Throwable $e) {
            Log::error('MCP tools/call falhou', ['ferramenta' => $nome, 'erro' => $e->getMessage()]);

            return $this->erro($id, self::ERRO_INTERNO, 'Erro interno ao executar a ferramenta.');
        }

        return $this->resultadoFerramenta($id, $texto, false);
    }

    private function resultadoFerramenta(mixed $id, string $texto, bool $erro): JsonResponse
    {
        return $this->resultado($id, [
            'content' => [['type' => 'text', 'text' => $texto]],
            'isError' => $erro,
        ]);
    }

    private function resultado(mixed $id, mixed $resultado): JsonResponse
    {
        return response()->json([
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => $resultado,
        ]);
    }

    private function erro(mixed $id, int $codigo, string $mensagem): JsonResponse
    {
        return response()->json([
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => ['code' => $codigo, 'message' => $mensagem],
        ]);
    }
}
