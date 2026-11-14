<?php

namespace Tests\Feature;

use App\Models\Pregacao;
use App\Models\PulpitoDispositivo;
use App\Models\Role;
use App\Models\User;
use App\Services\YouTubePregacoesImporter;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class PregacaoRoteiroTest extends TestCase
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

    private function pregacao(?User $pregador, array $attrs = []): Pregacao
    {
        $p = new Pregacao(array_merge([
            'titulo' => 'A videira', 'data_pregacao' => now()->toDateString(), 'pregador' => $pregador?->name, 'ativo' => true,
        ], $attrs));
        $p->pregador_id = $pregador?->id;
        $p->save();

        return $p;
    }

    private function dados(array $extra = []): array
    {
        return array_merge([
            'titulo' => 'A videira verdadeira',
            'data_pregacao' => '2026-11-15',
            'versiculo' => 'João 15:1-8',
            'roteiro' => '<p>Texto</p>',
            'roteiro_fonte' => 28,
        ], $extra);
    }

    // ─── Acesso ───

    public function test_membro_comum_nao_acessa(): void
    {
        $this->actingAs($this->usuario('membro'))->get('/admin/pregacoes')->assertForbidden();
        $this->actingAs($this->usuario('lider'))->post('/admin/pregacoes')->assertForbidden();
    }

    public function test_pregador_ve_so_as_suas_e_pastor_ve_todas(): void
    {
        $ana = $this->usuario('membro', true, 'Ana');
        $beto = $this->usuario('membro', true, 'Beto');
        $this->pregacao($ana, ['titulo' => 'Da Ana']);
        $this->pregacao($beto, ['titulo' => 'Do Beto']);
        $this->pregacao(null, ['titulo' => 'Importada', 'youtube_url' => 'https://youtu.be/x']);

        $this->actingAs($ana)->get('/admin/pregacoes')
            ->assertInertia(fn ($p) => $p->component('Admin/Pregacoes/Index')
                ->has('pregacoes', 1)->where('pregacoes.0.titulo', 'Da Ana')->where('ehPastor', false));

        $this->actingAs($this->usuario('pastor'))->get('/admin/pregacoes')
            ->assertInertia(fn ($p) => $p->has('pregacoes', 3)->where('ehPastor', true));
    }

    public function test_pregador_nao_mexe_na_pregacao_de_outro(): void
    {
        $ana = $this->usuario('membro', true, 'Ana');
        $beto = $this->usuario('membro', true, 'Beto');
        $p = $this->pregacao($ana);

        $this->actingAs($beto)->get("/admin/pregacoes/{$p->id}/edit")->assertForbidden();
        $this->actingAs($beto)->get("/admin/pregacoes/{$p->id}/pulpito")->assertForbidden();
        $this->actingAs($beto)->putJson("/admin/pregacoes/{$p->id}", $this->dados())->assertForbidden();
        $this->actingAs($beto)->delete("/admin/pregacoes/{$p->id}")->assertForbidden();
        $this->assertSame('A videira', $p->fresh()->titulo);
    }

    // ─── Fluxo ───

    public function test_nova_pregacao_vira_rascunho_do_pregador(): void
    {
        Carbon::setTestNow('2026-11-14 10:00'); // sábado
        $ana = $this->usuario('membro', true, 'Ana');

        $res = $this->actingAs($ana)->post('/admin/pregacoes');

        $p = Pregacao::sole();
        $res->assertRedirect("/admin/pregacoes/{$p->id}/edit");
        $this->assertSame($ana->id, $p->pregador_id);
        $this->assertSame('Ana', $p->pregador);
        $this->assertSame('2026-11-15', $p->data_pregacao->format('Y-m-d'));
        $this->assertNull($p->youtube_url);
        $this->assertFalse($p->publicada());
    }

    public function test_autosave_salva_roteiro_e_limpa_html_perigoso(): void
    {
        $ana = $this->usuario('membro', true);
        $p = $this->pregacao($ana);

        $html = '<h2>Ponto 1</h2><p><span style="background-color: rgba(250, 204, 21, 0.38)">marcado</span> '
            .'<span style="font-size: 1.3em">grande</span></p><script>alert(1)</script><p onclick="x()">c</p>';

        $this->actingAs($ana)->putJson("/admin/pregacoes/{$p->id}", $this->dados(['roteiro' => $html]))
            ->assertOk()->assertJsonPath('ok', true)->assertJsonPath('status', 'rascunho');

        $p->refresh();
        $this->assertSame(28, $p->roteiro_fonte);
        $this->assertStringContainsString('background-color', $p->roteiro);
        $this->assertStringContainsString('font-size:1.3em', str_replace(' ', '', $p->roteiro));
        $this->assertStringNotContainsString('script', $p->roteiro);
        $this->assertStringNotContainsString('onclick', $p->roteiro);
    }

    public function test_pregador_nao_publica_nem_troca_pregador(): void
    {
        $ana = $this->usuario('membro', true, 'Ana');
        $beto = $this->usuario('membro', true, 'Beto');
        $p = $this->pregacao($ana);

        $this->actingAs($ana)->putJson("/admin/pregacoes/{$p->id}", $this->dados([
            'youtube_url' => 'https://youtu.be/abc', 'ativo' => true, 'pregador_id' => $beto->id,
        ]))->assertOk();

        $p->refresh();
        $this->assertNull($p->youtube_url);
        $this->assertSame($ana->id, $p->pregador_id);
    }

    public function test_pastor_publica_com_youtube_e_escolhe_pregador(): void
    {
        $pastor = $this->usuario('pastor');
        $ana = $this->usuario('membro', true, 'Ana');
        $p = $this->pregacao($pastor);

        $this->actingAs($pastor)->putJson("/admin/pregacoes/{$p->id}", $this->dados([
            'youtube_url' => 'https://www.youtube.com/watch?v=abc', 'ativo' => true, 'pregador_id' => $ana->id, 'descricao' => 'Resumo',
        ]))->assertOk()->assertJsonPath('status', 'publicada');

        $p->refresh();
        $this->assertTrue($p->publicada());
        $this->assertSame('Ana', $p->pregador);
        $this->assertSame($ana->id, $p->pregador_id);

        // convidado: sem usuário, nome livre
        $this->actingAs($pastor)->putJson("/admin/pregacoes/{$p->id}", $this->dados([
            'youtube_url' => 'https://www.youtube.com/watch?v=abc', 'ativo' => true, 'pregador_id' => null, 'pregador' => 'Pr. Visitante',
        ]))->assertOk();
        $this->assertSame('Pr. Visitante', $p->fresh()->pregador);
        $this->assertNull($p->fresh()->pregador_id);

        $this->actingAs($pastor)->putJson("/admin/pregacoes/{$p->id}", $this->dados(['youtube_url' => 'nao-e-link']))
            ->assertStatus(422)->assertJsonValidationErrors('youtube_url');
    }

    public function test_rascunho_nao_aparece_no_site_nem_na_api(): void
    {
        $ana = $this->usuario('membro', true);
        $rascunho = $this->pregacao($ana, ['titulo' => 'Secreta', 'roteiro' => '<p>roteiro</p>']);
        $publicada = $this->pregacao(null, ['titulo' => 'Publica', 'youtube_url' => 'https://youtu.be/a']);

        $this->get("/pregacoes/{$rascunho->id}")->assertNotFound();
        $this->get("/pregacoes/{$publicada->id}")->assertOk();
        $this->get('/pregacoes')->assertInertia(fn ($p) => $p->has('pregacoes', 1)->where('pregacoes.0.titulo', 'Publica'));
        $this->actingAs($ana, 'sanctum')->getJson('/api/v1/pregacoes')->assertOk()->assertJsonCount(1, 'data');
        $this->actingAs($ana, 'sanctum')->getJson("/api/v1/pregacoes/{$rascunho->id}")->assertNotFound();
        $this->get('/sitemap.xml')->assertDontSee("/pregacoes/{$rascunho->id}<", false);
    }

    public function test_pregador_so_exclui_rascunho(): void
    {
        $ana = $this->usuario('membro', true);
        $rascunho = $this->pregacao($ana);
        $comVideo = $this->pregacao($ana, ['youtube_url' => 'https://youtu.be/a']);

        $this->actingAs($ana)->delete("/admin/pregacoes/{$comVideo->id}")->assertForbidden();
        $this->actingAs($ana)->delete("/admin/pregacoes/{$rascunho->id}")->assertRedirect('/admin/pregacoes');
        $this->assertDatabaseMissing('pregacoes', ['id' => $rascunho->id]);
    }

    public function test_modo_pulpito_abre_para_o_dono(): void
    {
        $ana = $this->usuario('membro', true);
        $p = $this->pregacao($ana, ['roteiro' => '<p>Olá</p>']);

        $this->actingAs($ana)->get("/admin/pregacoes/{$p->id}/pulpito")
            ->assertInertia(fn ($pg) => $pg->component('Admin/Pregacoes/Pulpito')->where('roteiro.conteudo', '<p>Olá</p>'));
    }

    public function test_link_antigo_de_roteiros_redireciona(): void
    {
        $this->actingAs($this->usuario('pastor'))->get('/admin/roteiros')->assertRedirect('/admin/pregacoes');
    }

    public function test_importar_youtube_preenche_rascunho_da_semana(): void
    {
        $ana = $this->usuario('membro', true);
        $rascunho = $this->pregacao($ana, ['titulo' => 'Título do pregador', 'data_pregacao' => '2026-11-15', 'roteiro' => '<p>x</p>']);

        $criar = new ReflectionMethod(YouTubePregacoesImporter::class, 'criarPregacao');
        $criar->invoke(app(YouTubePregacoesImporter::class), 'vid123', 'Culto 15/11', '2026-11-16T22:00:00Z', 'desc');
        $criar->invoke(app(YouTubePregacoesImporter::class), 'vid999', 'Outro vídeo', '2026-12-20T22:00:00Z', null);

        $rascunho->refresh();
        $this->assertSame('https://www.youtube.com/watch?v=vid123', $rascunho->youtube_url);
        $this->assertSame('Título do pregador', $rascunho->titulo);
        $this->assertSame('<p>x</p>', $rascunho->roteiro);
        $this->assertSame(2, Pregacao::count());
    }

    // ─── Permissão de pregador ───

    public function test_pastor_marca_pregador(): void
    {
        $pastor = $this->usuario('pastor');
        $joao = $this->usuario('membro');

        $this->actingAs($pastor)->put("/admin/usuarios/{$joao->id}", [
            'name' => $joao->name, 'email' => $joao->email, 'role_id' => $joao->role_id, 'is_pregador' => true,
        ])->assertRedirect('/admin/usuarios');

        $this->assertTrue($joao->fresh()->is_pregador);
        $this->actingAs($joao->fresh())->get('/admin/pregacoes')
            ->assertInertia(fn ($p) => $p->where('auth.user.pode_pregar', true));
    }

    public function test_membro_nao_se_marca_pregador_pelo_perfil(): void
    {
        $joao = $this->usuario('membro');
        $this->actingAs($joao)->put('/admin/perfil', ['name' => 'João', 'email' => $joao->email, 'is_pregador' => true]);
        $this->assertFalse((bool) $joao->fresh()->is_pregador);
    }

    // ─── API do iPad do púlpito ───

    public function test_api_exige_token_do_aparelho(): void
    {
        $this->getJson('/api/v1/pulpito/roteiros')->assertUnauthorized();
        $this->getJson('/api/v1/pulpito/roteiros', ['Authorization' => 'Bearer plp_errado'])->assertUnauthorized();
    }

    public function test_api_lista_pregacoes_com_roteiro_da_janela(): void
    {
        Carbon::setTestNow('2026-11-14 10:00');
        [, $token] = PulpitoDispositivo::criar('iPad do púlpito');
        $h = ['Authorization' => "Bearer {$token}"];
        $ana = $this->usuario('membro', true, 'Ana');

        $this->pregacao($ana, ['titulo' => 'Antiga', 'data_pregacao' => '2026-10-01', 'roteiro' => '<p>a</p>']);
        $this->pregacao($ana, ['titulo' => 'Semana passada', 'data_pregacao' => '2026-11-08', 'roteiro' => '<p>b</p>']);
        $this->pregacao($ana, ['titulo' => 'Sem roteiro', 'data_pregacao' => '2026-11-15']);
        $domingo = $this->pregacao($ana, ['titulo' => 'Domingo', 'data_pregacao' => '2026-11-15', 'roteiro' => '<p>Oi</p>', 'roteiro_fonte' => 30]);

        $this->getJson('/api/v1/pulpito/roteiros', $h)->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.titulo', 'Semana passada')
            ->assertJsonPath('data.1.titulo', 'Domingo')
            ->assertJsonPath('data.1.pregador', 'Ana')
            ->assertJsonMissingPath('data.1.conteudo');

        $this->getJson("/api/v1/pulpito/roteiros/{$domingo->id}", $h)->assertOk()
            ->assertJsonPath('data.conteudo', '<p>Oi</p>')
            ->assertJsonPath('data.tamanho_fonte', 30)
            ->assertJsonPath('data.data', '2026-11-15');
    }

    public function test_pastor_gera_e_desliga_token_do_pulpito(): void
    {
        $pastor = $this->usuario('pastor');
        $this->actingAs($this->usuario('membro', true))->get('/admin/pulpito')->assertForbidden();

        $this->actingAs($pastor)->post('/admin/pulpito', ['nome' => 'iPad do púlpito'])->assertSessionHas('pulpito_token');
        $token = session('pulpito_token')['token'];
        $this->getJson('/api/v1/pulpito', ['Authorization' => "Bearer {$token}"])->assertOk();

        $this->actingAs($pastor)->delete('/admin/pulpito/'.PulpitoDispositivo::sole()->id);
        $this->getJson('/api/v1/pulpito', ['Authorization' => "Bearer {$token}"])->assertUnauthorized();
    }
}
