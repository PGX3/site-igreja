<?php

namespace Tests\Feature;

use App\Models\Escala;
use App\Models\Grupo;
use App\Models\GrupoAviso;
use App\Models\Musica;
use App\Models\PedidoOracao;
use App\Models\Pregacao;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Mural do grupo, repertório, pregações, pedidos de oração e gestão de escalas pela API. */
class ApiEquipesTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $nome, string $role = 'membro'): User
    {
        return User::create([
            'name' => $nome,
            'email' => str()->slug($nome).'@exemplo.com',
            'tipo' => 'membro',
            'role_id' => Role::where('name', $role)->value('id'),
        ]);
    }

    private function grupo(User $lider, bool $temMusicas = true): Grupo
    {
        return Grupo::create(['nome' => 'Louvor '.$lider->id, 'lider_id' => $lider->id, 'created_by' => $lider->id, 'tem_musicas' => $temMusicas]);
    }

    private function escala(Grupo $grupo, string $data = '2026-11-01'): Escala
    {
        return Escala::create([
            'titulo' => 'Culto de domingo',
            'data' => $data,
            'hora_inicio' => '18:00',
            'hora_fim' => '20:00',
            'grupo_id' => $grupo->id,
            'status' => 'pendente',
            'created_by' => $grupo->lider_id,
        ]);
    }

    // ── Mural ──

    public function test_mural_so_para_quem_e_do_grupo(): void
    {
        $lider = $this->usuario('Lider', 'lider');
        $grupo = $this->grupo($lider);
        $membro = $this->usuario('Membro');
        $grupo->membros()->attach($membro);
        GrupoAviso::create(['grupo_id' => $grupo->id, 'autor_id' => $lider->id, 'titulo' => 'Antigo', 'corpo' => '...']);
        GrupoAviso::create(['grupo_id' => $grupo->id, 'autor_id' => $lider->id, 'titulo' => 'Fixado', 'corpo' => '...', 'fixado' => true]);

        Sanctum::actingAs($this->usuario('De Fora'));
        $this->getJson("/api/v1/grupos/{$grupo->id}/avisos")->assertForbidden();

        Sanctum::actingAs($membro);
        $this->getJson("/api/v1/grupos/{$grupo->id}/avisos")
            ->assertOk()
            ->assertJsonPath('data.pode_gerenciar', false)
            ->assertJsonPath('data.avisos.0.titulo', 'Fixado')
            ->assertJsonPath('data.avisos.1.autor.nome', 'Lider');
        $this->postJson("/api/v1/grupos/{$grupo->id}/avisos", ['titulo' => 'x', 'corpo' => 'y'])->assertForbidden();
    }

    public function test_lider_publica_e_remove_aviso(): void
    {
        $lider = $this->usuario('Lider', 'lider');
        $grupo = $this->grupo($lider);
        Sanctum::actingAs($lider);

        $id = $this->postJson("/api/v1/grupos/{$grupo->id}/avisos", ['titulo' => 'Ensaio', 'corpo' => 'Sábado 15h', 'fixado' => true])
            ->assertCreated()
            ->assertJsonPath('data.fixado', true)
            ->json('data.id');

        $this->postJson("/api/v1/grupos/{$grupo->id}/avisos", ['titulo' => ''])->assertUnprocessable();
        $this->deleteJson("/api/v1/grupos/{$grupo->id}/avisos/{$id}")->assertOk();
        $this->assertDatabaseMissing('grupo_avisos', ['id' => $id]);
    }

    // ── Músicas ──

    public function test_repertorio_lista_busca_e_mostra_letra(): void
    {
        Sanctum::actingAs($this->usuario('Musico'));
        $musica = Musica::create(['nome' => 'Grande é o Senhor', 'tom' => 'G', 'letra' => '<p>Letra</p>']);
        Musica::create(['nome' => 'Outra', 'letra' => '<p>...</p>']);

        $this->getJson('/api/v1/musicas?q=grande')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonMissingPath('data.0.letra');

        $this->getJson("/api/v1/musicas/{$musica->id}")
            ->assertOk()
            ->assertJsonPath('data.letra', '<p>Letra</p>')
            ->assertJsonPath('data.vezes', 0);
    }

    // ── Pregações ──

    public function test_pregacoes_so_ativas(): void
    {
        Sanctum::actingAs($this->usuario('Membro'));
        $ativa = Pregacao::create(['titulo' => 'Graça', 'pregador' => 'Leandro', 'youtube_url' => 'https://youtu.be/a', 'data_pregacao' => '2026-10-18', 'ativo' => true, 'descricao' => 'Texto']);
        $oculta = Pregacao::create(['titulo' => 'Rascunho', 'youtube_url' => 'https://youtu.be/b', 'data_pregacao' => '2026-10-19', 'ativo' => false]);

        $this->getJson('/api/v1/pregacoes')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.titulo', 'Graça');
        $this->getJson("/api/v1/pregacoes/{$ativa->id}")->assertOk()->assertJsonPath('data.descricao', 'Texto');
        $this->getJson("/api/v1/pregacoes/{$oculta->id}")->assertNotFound();
    }

    // ── Pedidos de oração ──

    public function test_pedidos_de_oracao_so_para_pastor(): void
    {
        $pedido = PedidoOracao::create(['nome' => 'Ana', 'pedido' => 'Saúde', 'status' => PedidoOracao::STATUS_NOVO]);

        Sanctum::actingAs($this->usuario('Lider', 'lider'));
        $this->getJson('/api/v1/pedidos-oracao')->assertForbidden();

        Sanctum::actingAs($this->usuario('Pastor', 'pastor'));
        $this->getJson('/api/v1/pedidos-oracao?status=novo')->assertOk()->assertJsonPath('data.0.pedido', 'Saúde');
        $this->patchJson("/api/v1/pedidos-oracao/{$pedido->id}/status", ['status' => 'em_oracao'])
            ->assertOk()
            ->assertJsonPath('data.status', 'em_oracao');
        $this->patchJson("/api/v1/pedidos-oracao/{$pedido->id}/status", ['status' => 'xyz'])->assertUnprocessable();
    }

    // ── Gestão de escalas ──

    public function test_gerenciadas_traz_so_escalas_dos_grupos_do_lider(): void
    {
        $lider = $this->usuario('Lider', 'lider');
        $meu = $this->escala($this->grupo($lider));
        $this->escala($this->grupo($this->usuario('Outro Lider', 'lider')));

        Sanctum::actingAs($lider);
        $this->getJson('/api/v1/escalas/gerenciadas')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $meu->id);

        Sanctum::actingAs($this->usuario('Pastor', 'pastor'));
        $this->getJson('/api/v1/escalas/gerenciadas')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_lider_monta_setlist_e_notas(): void
    {
        $lider = $this->usuario('Lider', 'lider');
        $escala = $this->escala($this->grupo($lider));
        $a = Musica::create(['nome' => 'A', 'letra' => '<p>a</p>']);
        $b = Musica::create(['nome' => 'B', 'letra' => '<p>b</p>']);
        Sanctum::actingAs($lider);

        $this->postJson("/api/v1/escalas/{$escala->id}/setlist", ['musica_id' => $a->id, 'tom' => 'D'])->assertOk();
        $this->postJson("/api/v1/escalas/{$escala->id}/setlist", ['musica_id' => $a->id])->assertUnprocessable();
        $res = $this->postJson("/api/v1/escalas/{$escala->id}/setlist", ['musica_id' => $b->id])
            ->assertOk()
            ->assertJsonCount(2, 'data.setlist');
        [$itemA, $itemB] = [$res->json('data.setlist.0.id'), $res->json('data.setlist.1.id')];

        $this->patchJson("/api/v1/escalas/{$escala->id}/setlist/reorder", ['itens' => [$itemB, $itemA]])
            ->assertOk()
            ->assertJsonPath('data.setlist.0.musica.nome', 'B');
        $this->patchJson("/api/v1/escalas/{$escala->id}/setlist/{$itemA}", ['tom' => 'E'])->assertOk();
        $this->deleteJson("/api/v1/escalas/{$escala->id}/setlist/{$itemB}")
            ->assertOk()
            ->assertJsonCount(1, 'data.setlist')
            ->assertJsonPath('data.setlist.0.tom', 'E');

        $notaId = $this->postJson("/api/v1/escalas/{$escala->id}/notas", ['corpo' => 'Chegar 17h'])
            ->assertOk()
            ->json('data.notas.0.id');
        $this->deleteJson("/api/v1/escalas/{$escala->id}/notas/{$notaId}")->assertOk()->assertJsonCount(0, 'data.notas');
    }

    public function test_membro_e_grupo_sem_musicas_nao_mexem_no_setlist(): void
    {
        $lider = $this->usuario('Lider', 'lider');
        $musica = Musica::create(['nome' => 'A', 'letra' => '<p>a</p>']);
        $escala = $this->escala($this->grupo($lider));
        $semMusicas = $this->escala($this->grupo($this->usuario('Lider Recepcao', 'lider'), temMusicas: false));

        Sanctum::actingAs($this->usuario('Membro'));
        $this->postJson("/api/v1/escalas/{$escala->id}/setlist", ['musica_id' => $musica->id])->assertForbidden();
        $this->postJson("/api/v1/escalas/{$escala->id}/notas", ['corpo' => 'x'])->assertForbidden();

        Sanctum::actingAs(User::where('name', 'Lider Recepcao')->first());
        $this->postJson("/api/v1/escalas/{$semMusicas->id}/setlist", ['musica_id' => $musica->id])->assertForbidden();
    }
}
