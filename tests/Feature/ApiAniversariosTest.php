<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiAniversariosTest extends TestCase
{
    use RefreshDatabase;

    private function membro(string $nome, ?string $nascimento, string $role = 'membro'): User
    {
        return User::create([
            'name' => $nome,
            'email' => str()->slug($nome).'@exemplo.com',
            'tipo' => 'membro',
            'data_nascimento' => $nascimento,
            'role_id' => Role::where('name', $role)->value('id'),
        ]);
    }

    public function test_sem_login_e_recusado(): void
    {
        $this->getJson('/api/v1/aniversarios')->assertUnauthorized();
    }

    public function test_membro_comum_nao_tem_acesso(): void
    {
        Sanctum::actingAs($this->membro('Membro Comum', null));

        $this->getJson('/api/v1/aniversarios')->assertForbidden();
    }

    public function test_lider_ve_semana_proximos_e_sem_data(): void
    {
        // Semana de 05/10/2026 (segunda) a 11/10/2026 (domingo), referência na quarta 07/10
        Sanctum::actingAs($this->membro('Lider Teste', null, 'lider'));
        $this->membro('Luciano Cabral', '1980-10-05');
        $this->membro('Anrieli Rocha', '1995-10-07');
        $this->membro('Fora da Semana', '1990-10-12');

        $this->getJson('/api/v1/aniversarios?data=2026-10-07')
            ->assertOk()
            ->assertJsonPath('data.semana_inicio', '2026-10-05')
            ->assertJsonCount(2, 'data.semana')
            ->assertJsonPath('data.semana.0.nome', 'Luciano Cabral')
            ->assertJsonPath('data.semana.0.ja_passou', true)
            ->assertJsonPath('data.semana.1.hoje', true)
            ->assertJsonPath('data.semana.1.idade', 31)
            ->assertJsonPath('data.proximos.0.nome', 'Anrieli Rocha')
            ->assertJsonPath('data.proximos.1.nome', 'Fora da Semana')
            ->assertJsonPath('data.proximos.1.dias_restantes', 5)
            ->assertJsonPath('data.proximos.2.proximo_aniversario', '2027-10-05')
            ->assertJsonPath('data.sem_data.0.nome', 'Lider Teste');
    }
}
