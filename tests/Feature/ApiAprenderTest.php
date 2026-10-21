<?php

namespace Tests\Feature;

use App\Models\Aula;
use App\Models\AulaComentario;
use App\Models\Curso;
use App\Models\CursoModulo;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiAprenderTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $role): User
    {
        return User::create([
            'name' => ucfirst($role).' Teste',
            'email' => $role.'@exemplo.com',
            'tipo' => 'membro',
            'role_id' => Role::where('name', $role)->value('id'),
        ]);
    }

    /** @return array{0: Curso, 1: Aula, 2: Aula} curso, aula visível e aula oculta */
    private function cursoComAulas(bool $ativo = true): array
    {
        $curso = Curso::create(['titulo' => 'Fundamentos', 'descricao' => '<p>Base da fé</p>', 'ativo' => $ativo]);
        $modulo = CursoModulo::create(['curso_id' => $curso->id, 'titulo' => 'Módulo 1']);
        $visivel = Aula::create(['modulo_id' => $modulo->id, 'titulo' => 'Aula 1', 'conteudo' => '<p>Texto</p>']);
        $oculta = Aula::create(['modulo_id' => $modulo->id, 'titulo' => 'Aula 2', 'ativo' => false, 'ordem' => 1]);

        return [$curso, $visivel, $oculta];
    }

    public function test_sem_login_e_recusado(): void
    {
        $this->getJson('/api/v1/aprender')->assertUnauthorized();
    }

    public function test_lista_so_cursos_ativos(): void
    {
        Sanctum::actingAs($this->usuario('membro'));
        $this->cursoComAulas();
        Curso::create(['titulo' => 'Rascunho', 'ativo' => false]);

        $this->getJson('/api/v1/aprender')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titulo', 'Fundamentos')
            ->assertJsonPath('data.0.descricao', 'Base da fé')
            ->assertJsonPath('data.0.modulos_count', 1);
    }

    public function test_curso_esconde_aulas_ocultas_do_membro(): void
    {
        Sanctum::actingAs($this->usuario('membro'));
        [$curso, $visivel] = $this->cursoComAulas();

        $this->getJson("/api/v1/aprender/{$curso->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data.modulos.0.aulas')
            ->assertJsonPath('data.modulos.0.aulas.0.url', route('api.v1.aprender.aula', $visivel->id));
    }

    public function test_pastor_ve_aulas_ocultas_e_curso_inativo(): void
    {
        Sanctum::actingAs($this->usuario('pastor'));
        [$curso] = $this->cursoComAulas(ativo: false);

        $this->getJson("/api/v1/aprender/{$curso->id}")
            ->assertOk()
            ->assertJsonPath('data.preview', true)
            ->assertJsonCount(2, 'data.modulos.0.aulas');
    }

    public function test_membro_nao_abre_curso_ou_aula_oculta(): void
    {
        Sanctum::actingAs($this->usuario('membro'));
        [$curso, , $oculta] = $this->cursoComAulas(ativo: false);

        $this->getJson("/api/v1/aprender/{$curso->id}")->assertNotFound();
        $this->getJson("/api/v1/aprender/aulas/{$oculta->id}")->assertNotFound();
    }

    public function test_aula_traz_conteudo_e_comentarios(): void
    {
        $membro = $this->usuario('membro');
        $outro = $this->usuario('lider');
        Sanctum::actingAs($membro);
        [$curso, $aula] = $this->cursoComAulas();
        AulaComentario::create(['aula_id' => $aula->id, 'user_id' => $outro->id, 'corpo' => 'Amém']);
        AulaComentario::create(['aula_id' => $aula->id, 'user_id' => $membro->id, 'corpo' => 'Muito bom']);

        $this->getJson("/api/v1/aprender/aulas/{$aula->id}")
            ->assertOk()
            ->assertJsonPath('data.conteudo', '<p>Texto</p>')
            ->assertJsonPath('data.curso_id', $curso->id)
            ->assertJsonPath('data.curso_titulo', 'Fundamentos')
            ->assertJsonCount(2, 'data.comentarios')
            ->assertJsonFragment(['corpo' => 'Muito bom', 'pode_excluir' => true])
            ->assertJsonFragment(['corpo' => 'Amém', 'pode_excluir' => false]);
    }
}
