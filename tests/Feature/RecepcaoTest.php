<?php

namespace Tests\Feature;

use App\Models\PedidoOracao;
use App\Models\RecepcaoDispositivo;
use App\Models\Role;
use App\Models\Sugestao;
use App\Models\User;
use App\Notifications\NovoVisitante;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RecepcaoTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    private RecepcaoDispositivo $dispositivo;

    protected function setUp(): void
    {
        parent::setUp();
        [$this->dispositivo, $this->token] = RecepcaoDispositivo::criar('iPad da recepção', '2468');
    }

    private function usuario(string $papel, string $tipo = 'membro', string $nome = 'Fulano'): User
    {
        return User::create([
            'name' => $nome,
            'email' => strtolower($papel).uniqid().'@teste.local',
            'password' => 'segredo123',
            'role_id' => Role::where('name', $papel)->value('id'),
            'tipo' => $tipo,
        ]);
    }

    /** @return array<string, mixed> */
    private function payload(array $sobrescreve = []): array
    {
        return array_merge([
            'uuid' => 'envio-1',
            'name' => '  Joana   da Silva ',
            'telefone' => '(51) 99282-4071',
            'data_nascimento' => '1992-04-17',
            'como_conheceu' => 'Convite de um amigo',
            'autoriza_contato' => true,
        ], $sobrescreve);
    }

    private function api(): self
    {
        return $this->withToken($this->token);
    }

    public function test_sem_token_ou_com_token_errado_nao_entra(): void
    {
        $this->getJson('/api/v1/recepcao')->assertUnauthorized();
        $this->withToken('rcp_errado')->postJson('/api/v1/recepcao/visitantes', $this->payload())->assertUnauthorized();
        $this->assertSame(0, User::visitantes()->count());
    }

    public function test_config_devolve_opcoes_e_marca_uso(): void
    {
        $this->api()->getJson('/api/v1/recepcao')
            ->assertOk()
            ->assertJsonPath('dispositivo', 'iPad da recepção')
            ->assertJsonPath('tem_pin', true)
            ->assertJsonPath('como_conheceu_opcoes', User::COMO_CONHECEU_OPCOES);

        $this->assertNotNull($this->dispositivo->fresh()->ultimo_uso_em);
    }

    public function test_cadastra_visitante_com_autorizacao_e_pedido_de_oracao(): void
    {
        Notification::fake();
        Carbon::setTestNow('2026-11-15 10:30');
        $pastor = $this->usuario('pastor');
        $pastor->deviceTokens()->create(['token' => 'fcm-pastor', 'platform' => 'android']);
        $semApp = $this->usuario('pastor', 'membro', 'Pastor sem app');

        $this->api()->postJson('/api/v1/recepcao/visitantes', $this->payload([
            'pedido_oracao' => 'Pela saúde da minha mãe',
        ]))
            ->assertCreated()
            ->assertJson(['ok' => true, 'retorno' => false, 'primeiro_nome' => 'Joana']);

        $v = User::visitantes()->sole();
        $this->assertSame('Joana da Silva', $v->name);
        $this->assertSame('2026-11-15', $v->primeira_visita->toDateString());
        $this->assertTrue($v->autoriza_contato);
        $this->assertNotNull($v->autoriza_contato_em);

        $pedido = PedidoOracao::sole();
        $this->assertSame('Joana da Silva', $pedido->nome);
        $this->assertSame('Pela saúde da minha mãe', $pedido->pedido);

        $this->assertSame(1, $this->dispositivo->fresh()->cadastros);
        Notification::assertSentTo($pastor, NovoVisitante::class);
        Notification::assertNotSentTo($semApp, NovoVisitante::class);

        Carbon::setTestNow();
    }

    public function test_guarda_quando_o_visitante_nao_autoriza_contato(): void
    {
        $this->api()->postJson('/api/v1/recepcao/visitantes', $this->payload(['autoriza_contato' => false]))
            ->assertCreated();

        $this->assertFalse(User::visitantes()->sole()->autoriza_contato);
    }

    public function test_reenvio_do_mesmo_uuid_nao_duplica_nada(): void
    {
        $payload = $this->payload(['pedido_oracao' => 'Emprego']);

        $this->api()->postJson('/api/v1/recepcao/visitantes', $payload)->assertCreated();
        $this->api()->postJson('/api/v1/recepcao/visitantes', $payload)->assertOk()->assertJsonPath('retorno', false);

        $this->assertSame(1, User::visitantes()->count());
        $this->assertSame(1, PedidoOracao::count());
    }

    public function test_mesmo_telefone_e_retorno_e_nao_mexe_em_membro(): void
    {
        $membro = $this->usuario('membro', 'membro', 'Joana Membro');
        $membro->update(['telefone' => '51992824071']);

        $this->api()->postJson('/api/v1/recepcao/visitantes', $this->payload(['uuid' => 'x2', 'autoriza_contato' => false]))
            ->assertOk()
            ->assertJsonPath('retorno', true);

        $membro->refresh();
        $this->assertSame('membro', $membro->tipo);
        $this->assertSame('Joana Membro', $membro->name);
        $this->assertNull($membro->autoriza_contato);
        $this->assertSame(0, User::visitantes()->count());
    }

    public function test_visitante_que_volta_atualiza_a_resposta_de_contato(): void
    {
        $this->api()->postJson('/api/v1/recepcao/visitantes', $this->payload(['autoriza_contato' => false]))->assertCreated();
        $this->api()->postJson('/api/v1/recepcao/visitantes', $this->payload(['uuid' => 'envio-2', 'autoriza_contato' => true]))
            ->assertOk()->assertJsonPath('retorno', true);

        $this->assertTrue(User::visitantes()->sole()->autoriza_contato);
    }

    public function test_valida_campos_obrigatorios(): void
    {
        $this->api()->postJson('/api/v1/recepcao/visitantes', ['uuid' => 'v'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'telefone', 'data_nascimento', 'como_conheceu', 'autoriza_contato']);
    }

    public function test_modo_diacono_exige_pin_correto(): void
    {
        $convidou = $this->usuario('membro', 'membro', 'Carlos');

        $this->api()->postJson('/api/v1/recepcao/diacono', ['pin' => '0000'])->assertForbidden();
        $this->api()->postJson('/api/v1/recepcao/diacono', ['pin' => '2468'])
            ->assertOk()
            ->assertJsonFragment(['id' => $convidou->id, 'name' => 'Carlos']);

        $extras = ['convidado_por_id' => $convidou->id, 'observacao' => 'Veio com a família'];

        $this->api()->postJson('/api/v1/recepcao/visitantes', $this->payload($extras + ['pin' => '1111']))
            ->assertForbidden();
        $this->assertSame(0, User::visitantes()->count());

        $this->api()->postJson('/api/v1/recepcao/visitantes', $this->payload($extras + ['pin' => '2468']))
            ->assertCreated();

        $v = User::visitantes()->sole();
        $this->assertSame($convidou->id, $v->convidado_por_id);
        $this->assertStringContainsString('Veio com a família', $v->observacoes_pastorais);
    }

    public function test_aba_oracao_cria_pedido_e_nao_duplica_reenvio(): void
    {
        $payload = ['uuid' => 'o1', 'pedido' => 'Pela minha família', 'nome' => 'Rita', 'compartilhar' => true];
        $this->api()->postJson('/api/v1/recepcao/oracao', $payload)->assertCreated();
        $this->api()->postJson('/api/v1/recepcao/oracao', $payload)->assertOk();

        $p = PedidoOracao::sole();
        $this->assertSame('Rita', $p->nome);
        $this->assertFalse($p->anonimo);
        $this->assertTrue($p->compartilhar);

        $this->api()->postJson('/api/v1/recepcao/oracao', ['uuid' => 'o2', 'pedido' => 'Emprego'])->assertCreated();
        $this->assertTrue(PedidoOracao::latest('id')->first()->anonimo);
        $this->assertSame('Anônimo', PedidoOracao::latest('id')->first()->nome);

        $this->api()->postJson('/api/v1/recepcao/oracao', ['uuid' => 'o3'])->assertJsonValidationErrors('pedido');
    }

    public function test_aba_sugestao_cria_sugestao(): void
    {
        $this->api()->postJson('/api/v1/recepcao/sugestao', ['uuid' => 's1', 'mensagem' => 'Mais cadeiras no fundo'])
            ->assertCreated();
        $s = Sugestao::sole();
        $this->assertSame('Anônimo (recepção)', $s->nome);
        $this->assertSame('Mais cadeiras no fundo', $s->mensagem);

        $this->withToken('rcp_errado')->postJson('/api/v1/recepcao/sugestao', ['mensagem' => 'oi oi oi'])->assertUnauthorized();
    }

    public function test_so_pastor_gerencia_os_aparelhos(): void
    {
        $lider = $this->usuario('lider');
        $this->actingAs($lider)->get('/admin/recepcao')->assertForbidden();

        $pastor = $this->usuario('pastor');
        $this->actingAs($pastor)->get('/admin/recepcao')->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Recepcao/Index')->has('dispositivos', 1));

        $this->actingAs($pastor)->post('/admin/recepcao', ['nome' => 'iPad 2', 'pin' => '12'])
            ->assertSessionHasErrors('pin');

        $this->actingAs($pastor)->post('/admin/recepcao', ['nome' => 'iPad 2', 'pin' => '1357'])
            ->assertRedirect('/admin/recepcao')
            ->assertSessionHas('recepcao_token');

        $novo = RecepcaoDispositivo::where('nome', 'iPad 2')->sole();
        $token = session('recepcao_token')['token'];
        $this->assertSame(RecepcaoDispositivo::hashToken($token), $novo->token_hash);
        $this->assertTrue($novo->pinConfere('1357'));

        $this->actingAs($pastor)->delete("/admin/recepcao/{$novo->id}")->assertRedirect();
        $this->withToken($token)->getJson('/api/v1/recepcao')->assertUnauthorized();
    }
}
