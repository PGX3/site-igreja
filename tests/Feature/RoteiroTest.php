<?php

namespace Tests\Feature;

use App\Models\PulpitoDispositivo;
use App\Models\Role;
use App\Models\Roteiro;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoteiroTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $papel, bool $pregador = false, string $nome = 'Fulano'): User
    {
        $user = User::create([
            'name' => $nome,
            'email' => $papel.uniqid().'@teste.local',
            'password' => 'segredo123',
            'role_id' => Role::where('name', $papel)->value('id'),
            'tipo' => 'membro',
        ]);
        $user->forceFill(['is_pregador' => $pregador])->save();

        return $user;
    }

    private function roteiro(User $autor, array $attrs = []): Roteiro
    {
        $r = new Roteiro(array_merge(['titulo' => 'A videira', 'data' => now()->toDateString()], $attrs));
        $r->user_id = $autor->id;
        $r->save();

        return $r;
    }

    private function dadosValidos(array $extra = []): array
    {
        return array_merge([
            'titulo' => 'A videira verdadeira',
            'data' => '2026-11-15',
            'versiculo' => 'João 15:1-8',
            'conteudo' => '<p>Texto</p>',
            'tamanho_fonte' => 28,
        ], $extra);
    }

    // ─── Acesso ───

    public function test_membro_comum_nao_acessa_roteiros(): void
    {
        $this->actingAs($this->usuario('membro'))->get('/admin/roteiros')->assertForbidden();
        $this->actingAs($this->usuario('lider'))->post('/admin/roteiros')->assertForbidden();
    }

    public function test_pastor_e_pregador_acessam(): void
    {
        $this->actingAs($this->usuario('pastor'))->get('/admin/roteiros')->assertOk();
        $this->actingAs($this->usuario('membro', pregador: true))->get('/admin/roteiros')->assertOk();
    }

    public function test_pregador_ve_so_os_seus_e_pastor_ve_todos(): void
    {
        $ana = $this->usuario('membro', true, 'Ana');
        $beto = $this->usuario('membro', true, 'Beto');
        $this->roteiro($ana, ['titulo' => 'Da Ana']);
        $this->roteiro($beto, ['titulo' => 'Do Beto']);

        $this->actingAs($ana)->get('/admin/roteiros')
            ->assertInertia(fn ($p) => $p->component('Admin/Roteiros/Index')
                ->has('roteiros', 1)->where('roteiros.0.titulo', 'Da Ana')->where('verTodos', false));

        $this->actingAs($this->usuario('pastor'))->get('/admin/roteiros')
            ->assertInertia(fn ($p) => $p->has('roteiros', 2)->where('verTodos', true));
    }

    public function test_pregador_nao_mexe_no_roteiro_de_outro(): void
    {
        $ana = $this->usuario('membro', true, 'Ana');
        $beto = $this->usuario('membro', true, 'Beto');
        $r = $this->roteiro($ana);

        $this->actingAs($beto)->get("/admin/roteiros/{$r->id}/edit")->assertForbidden();
        $this->actingAs($beto)->get("/admin/roteiros/{$r->id}/pulpito")->assertForbidden();
        $this->actingAs($beto)->putJson("/admin/roteiros/{$r->id}", $this->dadosValidos())->assertForbidden();
        $this->actingAs($beto)->delete("/admin/roteiros/{$r->id}")->assertForbidden();
        $this->assertDatabaseHas('roteiros', ['id' => $r->id, 'titulo' => 'A videira']);

        // pastor pode
        $this->actingAs($this->usuario('pastor'))->putJson("/admin/roteiros/{$r->id}", $this->dadosValidos())->assertOk();
    }

    // ─── Fluxo ───

    public function test_novo_roteiro_cria_rascunho_e_abre_editor(): void
    {
        Carbon::setTestNow('2026-11-14 10:00'); // sábado
        $ana = $this->usuario('membro', true);

        $res = $this->actingAs($ana)->post('/admin/roteiros');

        $r = Roteiro::sole();
        $res->assertRedirect("/admin/roteiros/{$r->id}/edit");
        $this->assertSame($ana->id, $r->user_id);
        $this->assertSame('2026-11-15', $r->data->format('Y-m-d')); // próximo domingo
        $this->assertSame(26, $r->fresh()->tamanho_fonte);
    }

    public function test_autosave_salva_e_limpa_html_perigoso(): void
    {
        $ana = $this->usuario('membro', true);
        $r = $this->roteiro($ana);

        $html = '<h2>Ponto 1</h2><p><span style="background-color: rgba(250, 204, 21, 0.38)">marcado</span> '
            .'<span style="font-size: 1.3em">grande</span> <strong>forte</strong></p>'
            .'<script>alert(1)</script><p onclick="x()">clique</p>';

        $this->actingAs($ana)
            ->putJson("/admin/roteiros/{$r->id}", $this->dadosValidos(['conteudo' => $html]))
            ->assertOk()->assertJsonPath('ok', true)->assertJsonStructure(['atualizado_em']);

        $r->refresh();
        $this->assertSame('A videira verdadeira', $r->titulo);
        $this->assertSame(28, $r->tamanho_fonte);
        $this->assertStringContainsString('background-color', $r->conteudo);
        $this->assertStringContainsString('font-size:1.3em', str_replace(' ', '', $r->conteudo));
        $this->assertStringContainsString('<strong>forte</strong>', $r->conteudo);
        $this->assertStringNotContainsString('script', $r->conteudo);
        $this->assertStringNotContainsString('onclick', $r->conteudo);
    }

    public function test_autosave_valida_campos(): void
    {
        $ana = $this->usuario('membro', true);
        $r = $this->roteiro($ana);

        $this->actingAs($ana)
            ->putJson("/admin/roteiros/{$r->id}", $this->dadosValidos(['titulo' => '', 'tamanho_fonte' => 99]))
            ->assertStatus(422)->assertJsonValidationErrors(['titulo', 'tamanho_fonte']);
    }

    public function test_modo_pulpito_abre_para_o_dono(): void
    {
        $ana = $this->usuario('membro', true);
        $r = $this->roteiro($ana, ['conteudo' => '<p>Olá</p>']);

        $this->actingAs($ana)->get("/admin/roteiros/{$r->id}/pulpito")
            ->assertInertia(fn ($p) => $p->component('Admin/Roteiros/Pulpito')->where('roteiro.conteudo', '<p>Olá</p>'));
    }

    public function test_excluir(): void
    {
        $ana = $this->usuario('membro', true);
        $r = $this->roteiro($ana);

        $this->actingAs($ana)->delete("/admin/roteiros/{$r->id}")->assertRedirect('/admin/roteiros');
        $this->assertDatabaseMissing('roteiros', ['id' => $r->id]);
    }

    // ─── Permissão de pregador ───

    public function test_pastor_marca_pregador_e_menu_aparece(): void
    {
        $pastor = $this->usuario('pastor');
        $joao = $this->usuario('membro');

        $this->actingAs($pastor)->put("/admin/usuarios/{$joao->id}", [
            'name' => $joao->name, 'email' => $joao->email, 'role_id' => $joao->role_id,
            'is_pregador' => true,
        ])->assertRedirect('/admin/usuarios');

        $this->assertTrue($joao->fresh()->is_pregador);
        $this->actingAs($joao->fresh())->get('/admin/roteiros')
            ->assertInertia(fn ($p) => $p->where('auth.user.pode_pregar', true));
    }

    public function test_membro_nao_se_marca_pregador_pelo_perfil(): void
    {
        $joao = $this->usuario('membro');

        $this->actingAs($joao)->put('/admin/perfil', ['name' => 'João', 'email' => $joao->email, 'is_pregador' => true]);

        $this->assertFalse((bool) $joao->fresh()->is_pregador);
        $this->assertFalse(in_array('is_pregador', $joao->getFillable(), true));
    }

    // ─── API do iPad do púlpito ───

    public function test_api_exige_token_do_aparelho(): void
    {
        $this->getJson('/api/v1/pulpito/roteiros')->assertUnauthorized();
        $this->getJson('/api/v1/pulpito/roteiros', ['Authorization' => 'Bearer plp_errado'])->assertUnauthorized();
    }

    public function test_api_lista_roteiros_da_janela_e_baixa_conteudo(): void
    {
        Carbon::setTestNow('2026-11-14 10:00');
        [, $token] = PulpitoDispositivo::criar('iPad do púlpito');
        $h = ['Authorization' => "Bearer {$token}"];
        $ana = $this->usuario('membro', true, 'Ana');

        $this->roteiro($ana, ['titulo' => 'Antigo', 'data' => '2026-10-01']);
        $this->roteiro($ana, ['titulo' => 'Semana passada', 'data' => '2026-11-08']);
        $domingo = $this->roteiro($ana, ['titulo' => 'Domingo', 'data' => '2026-11-15', 'conteudo' => '<p>Oi</p>', 'tamanho_fonte' => 30]);
        $this->roteiro($ana, ['titulo' => 'Longe', 'data' => '2027-02-01']);

        $this->getJson('/api/v1/pulpito', $h)->assertOk()->assertJsonPath('dispositivo', 'iPad do púlpito');

        $this->getJson('/api/v1/pulpito/roteiros', $h)->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.titulo', 'Semana passada')
            ->assertJsonPath('data.1.titulo', 'Domingo')
            ->assertJsonPath('data.1.pregador', 'Ana')
            ->assertJsonMissingPath('data.1.conteudo');

        $this->getJson("/api/v1/pulpito/roteiros/{$domingo->id}", $h)->assertOk()
            ->assertJsonPath('data.conteudo', '<p>Oi</p>')
            ->assertJsonPath('data.tamanho_fonte', 30);

        $this->assertNotNull(PulpitoDispositivo::sole()->ultimo_uso_em);
    }

    public function test_pastor_gera_e_desliga_token_do_pulpito(): void
    {
        $pastor = $this->usuario('pastor');

        $this->actingAs($this->usuario('membro', true))->get('/admin/pulpito')->assertForbidden();

        $this->actingAs($pastor)->post('/admin/pulpito', ['nome' => 'iPad do púlpito'])
            ->assertRedirect('/admin/pulpito')->assertSessionHas('pulpito_token');
        $token = session('pulpito_token')['token'];
        $this->assertStringStartsWith('plp_', $token);
        $this->getJson('/api/v1/pulpito', ['Authorization' => "Bearer {$token}"])->assertOk();

        $d = PulpitoDispositivo::sole();
        $this->assertNotSame($token, $d->token_hash);
        $this->actingAs($pastor)->delete("/admin/pulpito/{$d->id}");
        $this->getJson('/api/v1/pulpito', ['Authorization' => "Bearer {$token}"])->assertUnauthorized();
    }
}
