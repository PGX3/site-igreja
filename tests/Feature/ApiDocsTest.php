<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiDocsTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_vai_para_o_login(): void
    {
        $this->get('/docs/api')->assertRedirect('/login');
    }

    public function test_usuario_logado_ve_a_documentacao_sem_as_rotas_da_automacao(): void
    {
        $user = User::create(['name' => 'Dev', 'email' => 'dev@exemplo.com', 'tipo' => 'membro']);

        $this->actingAs($user)->get('/docs/api')->assertOk();

        $paths = array_keys($this->actingAs($user)->getJson('/docs/api.json')->assertOk()->json('paths'));
        $this->assertContains('/escalas', $paths);
        $this->assertEmpty(array_filter($paths, fn ($p) => str_starts_with($p, '/automacao')));
    }
}
