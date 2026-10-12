<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutomacaoAniversariantesTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'token-de-teste-automacao';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.automacao.token' => self::TOKEN]);
    }

    private function membro(string $nome, ?string $nascimento): User
    {
        return User::create([
            'name' => $nome,
            'email' => str()->slug($nome).'@exemplo.com',
            'tipo' => 'membro',
            'data_nascimento' => $nascimento,
        ]);
    }

    public function test_sem_token_configurado_a_rota_nao_existe(): void
    {
        config(['services.automacao.token' => null]);

        $this->getJson('/api/v1/automacao/aniversariantes-semana')->assertNotFound();
    }

    public function test_token_invalido_e_recusado(): void
    {
        $this->getJson('/api/v1/automacao/aniversariantes-semana', ['Authorization' => 'Bearer errado'])
            ->assertUnauthorized();
    }

    public function test_lista_e_mensagem_da_semana(): void
    {
        // Semana de 05/10/2026 (segunda) a 11/10/2026 (domingo)
        $this->membro('Anrieli da Silva Rocha', '1995-10-07');
        $this->membro('Luciano Cabral', '1980-10-05');
        $this->membro('Fora da Semana', '1990-10-12');
        $this->membro('Sem Data', null);

        $response = $this->getJson('/api/v1/automacao/aniversariantes-semana?data=2026-10-11', [
            'Authorization' => 'Bearer '.self::TOKEN,
        ])->assertOk();

        $response->assertJsonPath('semana_inicio', '2026-10-05')
            ->assertJsonPath('semana_fim', '2026-10-11')
            ->assertJsonPath('total', 2)
            ->assertJsonPath('aniversariantes.0.nome', 'Luciano Cabral')
            ->assertJsonPath('aniversariantes.1.dia_semana', 'quarta-feira');

        $mensagem = $response->json('mensagem');
        $this->assertStringContainsString('🎂 Luciano Cabral - Segunda-feira (dia 5)', $mensagem);
        $this->assertStringContainsString('🎂 Anrieli Rocha - Quarta-feira (dia 7)', $mensagem);
        $this->assertStringNotContainsString('Fora', $mensagem);
    }

    public function test_semana_sem_aniversariantes_devolve_mensagem_nula(): void
    {
        $this->getJson('/api/v1/automacao/aniversariantes-semana?data=2026-01-05', [
            'Authorization' => 'Bearer '.self::TOKEN,
        ])->assertOk()->assertJsonPath('total', 0)->assertJsonPath('mensagem', null);
    }
}
