<?php

namespace Tests\Feature;

use App\Models\Culto;
use App\Models\CultoExcecao;
use App\Models\Evento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutomacaoAgendaTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'token-de-teste-automacao';

    private Culto $jovem;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.automacao.token' => self::TOKEN]);

        Culto::create(['nome' => 'Culto de Domingo', 'dia_semana' => 'Domingo', 'horario' => '19:00', 'hora_inicio' => '19:00:00', 'ativo' => true]);
        Culto::create(['nome' => 'Culto de Oração', 'dia_semana' => 'Quarta', 'horario' => '20:00', 'hora_inicio' => '20:00:00', 'ativo' => true]);
        $this->jovem = Culto::create(['nome' => 'Culto Jovem', 'dia_semana' => 'Sábado', 'horario' => '19:30', 'hora_inicio' => '19:30:00', 'ativo' => true]);
        Culto::create(['nome' => 'Culto Desativado', 'dia_semana' => 'Sexta', 'horario' => '20:00', 'ativo' => false]);
    }

    private function auth(): array
    {
        return ['Authorization' => 'Bearer '.self::TOKEN];
    }

    private function agenda(string $data)
    {
        return $this->getJson('/api/v1/automacao/agenda-semana?data='.$data, $this->auth())->assertOk();
    }

    public function test_exige_token(): void
    {
        $this->getJson('/api/v1/automacao/agenda-semana')->assertUnauthorized();
        $this->postJson('/api/v1/automacao/culto-excecoes', [])->assertUnauthorized();
    }

    public function test_cultos_e_eventos_da_semana_em_ordem(): void
    {
        Evento::create(['nome' => '4º Encontro de Mulheres', 'data_evento' => '2026-10-17', 'horario' => '15h', 'ativo' => true]);
        Evento::create(['nome' => 'Encontro de Homens', 'data_evento' => '2026-10-23', 'horario' => '20h', 'ativo' => true]);
        Evento::create(['nome' => 'Evento Inativo', 'data_evento' => '2026-10-15', 'ativo' => false]);

        $r = $this->agenda('2026-10-12')
            ->assertJsonPath('semana_inicio', '2026-10-12')
            ->assertJsonPath('total', 4)
            ->assertJsonPath('itens.0.nome', 'Culto de Oração')
            ->assertJsonPath('itens.1.nome', '4º Encontro de Mulheres')
            ->assertJsonPath('itens.2.nome', 'Culto Jovem')
            ->assertJsonPath('itens.3.data', '2026-10-18');

        $this->assertSame(implode("\n", [
            '📅 AGENDA DA SEMANA — IGREJA EM CHARQUEADAS',
            '',
            '🙏 QUARTA | 20h',
            'Culto de Oração',
            '',
            '📌 SÁBADO (17/10) | 15h',
            '4º Encontro de Mulheres',
            '',
            '🔥 SÁBADO | 19h30',
            'Culto Jovem',
            '',
            '⛪ DOMINGO | 19h',
            'Culto de Domingo',
            '',
            '❤️ Esperamos vocês!',
        ]), $r->json('mensagem'));
    }

    public function test_culto_remarcado_para_sexta(): void
    {
        // Qualquer dia da semana serve como `data`: normaliza para o sábado 17/10
        $this->postJson('/api/v1/automacao/culto-excecoes', [
            'culto_id' => $this->jovem->id,
            'data' => '2026-10-12',
            'nova_data' => '2026-10-16',
            'novo_horario' => '20:00',
        ], $this->auth())->assertCreated()->assertJsonPath('excecao.data', '2026-10-17');

        $msg = $this->agenda('2026-10-14')->json('mensagem');

        $this->assertStringContainsString(
            "🔥 SEXTA | 20h\nCulto Jovem\n⚠️ Excepcionalmente nesta semana será na sexta-feira, às 20h.",
            $msg,
        );
        $this->assertStringNotContainsString('SÁBADO', $msg);

        // Semana seguinte volta ao normal
        $this->assertStringContainsString("🔥 SÁBADO | 19h30\nCulto Jovem\n\n", $this->agenda('2026-10-19')->json('mensagem'));
    }

    public function test_observacao_personalizada_e_cancelamento(): void
    {
        $this->postJson('/api/v1/automacao/culto-excecoes', [
            'culto_id' => $this->jovem->id,
            'data' => '2026-10-17',
            'cancelado' => true,
            'observacao' => 'Nesta semana estaremos no Encontro de Mulheres.',
        ], $this->auth())->assertCreated();

        $msg = $this->agenda('2026-10-12')->json('mensagem');
        $this->assertStringContainsString("🔥 SÁBADO\n~Culto Jovem~\n⚠️ Nesta semana estaremos no Encontro de Mulheres.", $msg);

        // Salvar de novo atualiza em vez de duplicar; DELETE remove
        $this->postJson('/api/v1/automacao/culto-excecoes', [
            'culto_id' => $this->jovem->id, 'data' => '2026-10-17', 'novo_horario' => '18h',
        ], $this->auth())->assertOk();
        $this->assertSame(1, CultoExcecao::count());
        $this->assertStringContainsString('⚠️ Excepcionalmente nesta semana será às 18h.', $this->agenda('2026-10-12')->json('mensagem'));

        $id = $this->getJson('/api/v1/automacao/culto-excecoes?data=2026-10-12', $this->auth())->assertOk()->json('0.id');
        $this->deleteJson('/api/v1/automacao/culto-excecoes/'.$id, [], $this->auth())->assertOk();
        $this->assertSame(0, CultoExcecao::count());
    }

    public function test_lista_cultos(): void
    {
        $this->getJson('/api/v1/automacao/cultos', $this->auth())
            ->assertOk()
            ->assertJsonCount(4)
            ->assertJsonPath('2.nome', 'Culto Jovem')
            ->assertJsonPath('2.horario', '19h30');
    }
}
