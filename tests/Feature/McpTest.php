<?php

namespace Tests\Feature;

use App\Models\Culto;
use App\Models\Evento;
use App\Models\Pregacao;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class McpTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'token-de-teste-do-mcp';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'mcp.enabled' => true,
            'mcp.token' => self::TOKEN,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function rpc(array $payload, ?string $token = self::TOKEN)
    {
        $headers = $token !== null ? ['Authorization' => 'Bearer '.$token] : [];

        return $this->postJson('/api/mcp', $payload, $headers);
    }

    /**
     * Texto devolvido dentro do primeiro bloco de content de tools/call.
     *
     * @param  array<string, mixed>  $argumentos
     * @return array<string, mixed>
     */
    private function chamar(string $ferramenta, array $argumentos = []): array
    {
        $response = $this->rpc([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => ['name' => $ferramenta, 'arguments' => $argumentos],
        ]);

        $response->assertOk();

        return $response->json('result');
    }

    // ─────────────────────────────── Acesso ───────────────────────────────

    public function test_rota_nao_existe_com_mcp_desligado(): void
    {
        config(['mcp.enabled' => false]);

        $this->rpc(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'ping'])->assertNotFound();
    }

    public function test_exige_token(): void
    {
        $this->rpc(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'ping'], token: null)
            ->assertUnauthorized()
            ->assertHeader('WWW-Authenticate', 'Bearer realm="mcp"');
    }

    public function test_recusa_token_errado(): void
    {
        $this->rpc(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'ping'], token: 'errado')
            ->assertUnauthorized();
    }

    public function test_recusa_quando_habilitado_sem_token_configurado(): void
    {
        config(['mcp.token' => null]);

        $this->rpc(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'ping'])->assertStatus(503);
    }

    // ───────────────────────────── Protocolo ─────────────────────────────

    public function test_initialize_devolve_versao_e_dados_do_servidor(): void
    {
        $response = $this->rpc([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
            'params' => ['protocolVersion' => '2025-06-18'],
        ]);

        $response->assertOk()
            ->assertJsonPath('jsonrpc', '2.0')
            ->assertJsonPath('id', 1)
            ->assertJsonPath('result.protocolVersion', '2025-06-18')
            ->assertJsonPath('result.serverInfo.name', config('mcp.servidor.nome'))
            ->assertJsonStructure(['result' => ['capabilities', 'instructions']]);
    }

    public function test_initialize_com_versao_desconhecida_devolve_a_mais_recente_suportada(): void
    {
        $this->rpc([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
            'params' => ['protocolVersion' => '1999-01-01'],
        ])->assertJsonPath('result.protocolVersion', config('mcp.protocolos')[0]);
    }

    public function test_notificacao_nao_recebe_corpo(): void
    {
        $this->rpc(['jsonrpc' => '2.0', 'method' => 'notifications/initialized'])
            ->assertNoContent(202);
    }

    public function test_json_invalido_devolve_erro_de_parse(): void
    {
        $this->call(
            'POST',
            '/api/mcp',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.self::TOKEN,
            ],
            content: '{isso nao e json',
        )->assertOk()->assertJsonPath('error.code', -32700);
    }

    public function test_lote_de_requisicoes_e_recusado(): void
    {
        $this->rpc([['jsonrpc' => '2.0', 'id' => 1, 'method' => 'ping']])
            ->assertJsonPath('error.code', -32600);
    }

    public function test_metodo_desconhecido_devolve_erro(): void
    {
        $this->rpc(['jsonrpc' => '2.0', 'id' => 7, 'method' => 'resources/list'])
            ->assertJsonPath('id', 7)
            ->assertJsonPath('error.code', -32601);
    }

    public function test_tools_list_expoe_as_ferramentas_de_leitura(): void
    {
        $response = $this->rpc(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/list']);

        $nomes = array_column($response->assertOk()->json('result.tools'), 'name');

        $this->assertSame([
            'info_igreja',
            'listar_cultos',
            'listar_eventos',
            'buscar_pregacoes',
            'ler_pregacao',
        ], $nomes);
    }

    public function test_ferramenta_desconhecida_devolve_erro_de_parametro(): void
    {
        $this->rpc([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => ['name' => 'apagar_tudo'],
        ])->assertJsonPath('error.code', -32602);
    }

    // ───────────────────────────── Ferramentas ─────────────────────────────

    public function test_listar_cultos_traz_horario_e_proxima_data(): void
    {
        Culto::create([
            'nome' => 'Culto de Celebração',
            'dia_semana' => 'Domingo',
            'horario' => '19:00:00',
            'descricao' => '<p>Você é <strong>bem-vindo</strong>.</p>',
            'ativo' => true,
        ]);
        Culto::create([
            'nome' => 'Culto de Oração',
            'dia_semana' => 'Quarta',
            'horario' => '20:00',
            'ativo' => false,
        ]);

        $dados = json_decode($this->chamar('listar_cultos')['content'][0]['text'], true);

        $this->assertSame(1, $dados['total']);
        $this->assertSame('Culto de Celebração', $dados['cultos'][0]['nome']);
        $this->assertSame('19:00', $dados['cultos'][0]['horario']);
        $this->assertSame('Você é bem-vindo.', $dados['cultos'][0]['descricao']);
        $this->assertNotEmpty($dados['cultos'][0]['proxima_data']);
    }

    public function test_listar_eventos_ignora_passados_e_respeita_limite(): void
    {
        Evento::create([
            'nome' => 'Evento antigo',
            'data_evento' => Carbon::today()->subDay(),
            'ativo' => true,
        ]);

        foreach ([1, 2, 3] as $dias) {
            Evento::create([
                'nome' => "Evento em {$dias} dias",
                'data_evento' => Carbon::today()->addDays($dias),
                'horario' => '19:30',
                'local' => 'Templo',
                'ativo' => true,
            ]);
        }

        $dados = json_decode($this->chamar('listar_eventos', ['limite' => 2])['content'][0]['text'], true);

        $this->assertSame(2, $dados['total']);
        $this->assertSame('Evento em 1 dias', $dados['eventos'][0]['nome']);
        $this->assertStringContainsString('/eventos/', $dados['eventos'][0]['url']);
    }

    public function test_limite_fora_da_faixa_devolve_erro_de_parametro_legivel(): void
    {
        $response = $this->rpc([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => ['name' => 'listar_eventos', 'arguments' => ['limite' => 999]],
        ])->assertJsonPath('error.code', -32602);

        // O projeto não publica arquivos de tradução: a mensagem não pode vazar a chave crua.
        $mensagem = $response->json('error.message');
        $this->assertStringNotContainsString('validation.', $mensagem);
        $this->assertStringContainsString('limite', $mensagem);
    }

    public function test_buscar_pregacoes_filtra_por_termo_e_so_publicadas(): void
    {
        Pregacao::create([
            'titulo' => 'A graça de Deus',
            'pregador' => 'Pastor João',
            'youtube_url' => 'https://youtu.be/graca',
            'data_pregacao' => Carbon::today()->subWeek(),
            'ativo' => true,
        ]);
        Pregacao::create([
            'titulo' => 'Rascunho oculto',
            'pregador' => 'Pastor João',
            'youtube_url' => 'https://youtu.be/rascunho',
            'data_pregacao' => Carbon::today(),
            'ativo' => false,
        ]);

        $dados = json_decode($this->chamar('buscar_pregacoes', ['termo' => 'graça'])['content'][0]['text'], true);

        $this->assertSame(1, $dados['total']);
        $this->assertSame('A graça de Deus', $dados['pregacoes'][0]['titulo']);

        $todas = json_decode($this->chamar('buscar_pregacoes')['content'][0]['text'], true);
        $this->assertSame(1, $todas['total']);
    }

    public function test_ler_pregacao_devolve_conteudo_completo(): void
    {
        $pregacao = Pregacao::create([
            'titulo' => 'Esperança viva',
            'pregador' => 'Pastor João',
            'versiculo' => '1 Pedro 1:3',
            'youtube_url' => 'https://youtu.be/abc123',
            'descricao' => '<p>Primeiro parágrafo.</p><p>Segundo parágrafo.</p>',
            'data_pregacao' => Carbon::today(),
            'ativo' => true,
        ]);

        $dados = json_decode($this->chamar('ler_pregacao', ['id' => $pregacao->id])['content'][0]['text'], true);

        $this->assertSame('Esperança viva', $dados['titulo']);
        $this->assertSame('1 Pedro 1:3', $dados['versiculo']);
        $this->assertSame('Primeiro parágrafo. Segundo parágrafo.', $dados['descricao']);
    }

    public function test_ler_pregacao_inexistente_volta_como_is_error(): void
    {
        $resultado = $this->chamar('ler_pregacao', ['id' => 999]);

        $this->assertTrue($resultado['isError']);
        $this->assertStringContainsString('não encontrada', $resultado['content'][0]['text']);
    }

    public function test_ler_pregacao_exige_id(): void
    {
        $response = $this->rpc([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => ['name' => 'ler_pregacao', 'arguments' => []],
        ])->assertJsonPath('error.code', -32602);

        $this->assertStringNotContainsString('validation.', $response->json('error.message'));
    }

    public function test_info_igreja_nao_expoe_cnpj(): void
    {
        $texto = $this->chamar('info_igreja')['content'][0]['text'];

        $this->assertStringNotContainsString('cnpj', $texto);
        $this->assertArrayHasKey('telefone', json_decode($texto, true));
        // Sem textos cadastrados o campo continua sendo objeto, não lista.
        $this->assertStringContainsString('"textos_do_site": {}', $texto);
    }
}
