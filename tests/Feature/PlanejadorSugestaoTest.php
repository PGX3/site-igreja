<?php

namespace Tests\Feature;

use App\Models\Escala;
use App\Models\Grupo;
use App\Models\GrupoFuncao;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class PlanejadorSugestaoTest extends TestCase
{
    use RefreshDatabase;

    private const DOMINGOS = ['2026-11-01', '2026-11-08', '2026-11-15', '2026-11-22'];

    private User $pastor;

    private User $ana;

    private User $bia;

    private User $caio;

    private Grupo $grupo;

    private GrupoFuncao $camera;

    private GrupoFuncao $som;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        config(['services.deepseek.api_key' => null]);

        $this->pastor = $this->usuario('Pastor', 'pastor');
        $this->ana = $this->usuario('Ana Souza', 'membro');
        $this->bia = $this->usuario('Bia Lima', 'membro');
        $this->caio = $this->usuario('Caio Reis', 'membro');

        $this->grupo = Grupo::create(['nome' => 'Mídias', 'created_by' => $this->pastor->id]);
        $this->camera = GrupoFuncao::create(['grupo_id' => $this->grupo->id, 'nome' => 'Camera']);
        $this->som = GrupoFuncao::create(['grupo_id' => $this->grupo->id, 'nome' => 'Som']);
        $this->grupo->membros()->attach([$this->ana->id, $this->bia->id, $this->caio->id]);
    }

    private function usuario(string $nome, string $role): User
    {
        return User::create([
            'name' => $nome,
            'email' => uniqid().'@teste.com',
            'password' => 'segredo123',
            'role_id' => Role::where('name', $role)->value('id'),
        ]);
    }

    private function linhas(array $porData = []): array
    {
        return array_map(fn ($d) => [
            'data' => $d,
            'vinculo' => 'Culto de Domingo',
            'restricoes' => $porData[$d]['restricoes'] ?? '',
            'cells' => $porData[$d]['cells'] ?? [],
        ], self::DOMINGOS);
    }

    private function sugerir(array $linhas, ?User $quem = null)
    {
        return $this->actingAs($quem ?? $this->pastor)->postJson('/admin/planejador/sugerir', [
            'grupo_id' => $this->grupo->id,
            'mes' => '2026-11',
            'rows' => $linhas,
        ]);
    }

    /** linha => [funcao => user_id] */
    private function grade(array $json): array
    {
        $grade = [];
        foreach ($json['sugestoes'] as $s) {
            $grade[$s['linha']][$s['funcao']] = $s['user_id'];
        }

        return $grade;
    }

    private function escalar(User $user, string $data, string $funcao, ?Grupo $grupo = null): void
    {
        $escala = Escala::create([
            'titulo' => 'Culto', 'data' => $data, 'hora_inicio' => '19:00', 'hora_fim' => '21:00',
            'grupo_id' => ($grupo ?? $this->grupo)->id, 'status' => 'pendente', 'created_by' => $this->pastor->id,
        ]);
        $escala->escalaMembros()->create(['user_id' => $user->id, 'funcao' => $funcao]);
    }

    public function test_so_sugere_quem_faz_a_funcao(): void
    {
        $this->camera->membros()->sync([$this->ana->id, $this->bia->id]);
        $this->som->membros()->sync([$this->caio->id]);

        $grade = $this->grade($this->sugerir($this->linhas())->assertOk()->json());

        foreach ($grade as $celulas) {
            $this->assertContains($celulas['Camera'], [$this->ana->id, $this->bia->id]);
            $this->assertSame($this->caio->id, $celulas['Som']);
        }
        $this->assertCount(4, $grade);
    }

    public function test_nao_coloca_a_mesma_pessoa_em_duas_funcoes_no_mesmo_dia(): void
    {
        // Ana faz as duas. Sem mais ninguém para a segunda função, ela fica vazia.
        $this->camera->membros()->sync([$this->ana->id]);
        $this->som->membros()->sync([$this->ana->id]);

        $json = $this->sugerir($this->linhas())->json();

        foreach ($this->grade($json) as $celulas) {
            $this->assertCount(1, $celulas);
        }
        $this->assertCount(4, $json['sem_candidato']);
    }

    public function test_distribui_de_forma_equilibrada_e_sem_semanas_seguidas(): void
    {
        $this->som->delete();
        $this->camera->membros()->sync([$this->ana->id, $this->bia->id]);

        $grade = $this->grade($this->sugerir($this->linhas())->json());
        $camera = array_column($grade, 'Camera');

        $this->assertSame(2, count(array_keys($camera, $this->ana->id)));
        $this->assertSame(2, count(array_keys($camera, $this->bia->id)));
        for ($i = 1; $i < 4; $i++) {
            $this->assertNotSame($grade[$i - 1]['Camera'], $grade[$i]['Camera']);
        }
    }

    public function test_nao_mexe_no_que_ja_esta_preenchido_e_conta_na_carga(): void
    {
        $this->som->delete();

        // Ana já está nos dois primeiros domingos: os outros dois vão para Bia e Caio
        $json = $this->sugerir($this->linhas([
            '2026-11-01' => ['cells' => ['Camera' => $this->ana->id]],
            '2026-11-08' => ['cells' => ['Camera' => $this->ana->id]],
        ]))->json();

        $grade = $this->grade($json);
        $this->assertSame([2, 3], array_keys($grade));
        $this->assertEqualsCanonicalizing(
            [$this->bia->id, $this->caio->id],
            [$grade[2]['Camera'], $grade[3]['Camera']],
        );
    }

    public function test_quem_serviu_menos_nos_meses_anteriores_entra_primeiro(): void
    {
        $this->som->delete();
        $this->escalar($this->ana, '2026-10-04', 'Camera');
        $this->escalar($this->ana, '2026-10-11', 'Camera');
        $this->escalar($this->bia, '2026-10-18', 'Camera');

        $grade = $this->grade($this->sugerir($this->linhas())->json());

        $this->assertSame($this->caio->id, $grade[0]['Camera']);
        $this->assertSame($this->bia->id, $grade[1]['Camera']);
        $this->assertSame($this->ana->id, $grade[2]['Camera']);
    }

    public function test_pula_quem_esta_em_outro_grupo_no_dia(): void
    {
        $this->som->delete();
        $this->camera->membros()->sync([$this->ana->id]);

        $louvor = Grupo::create(['nome' => 'Louvor', 'created_by' => $this->pastor->id]);
        $this->escalar($this->ana, '2026-11-08', 'Vocal', $louvor);

        $json = $this->sugerir($this->linhas())->json();

        $this->assertArrayNotHasKey(1, $this->grade($json));
        $this->assertSame([['linha' => 1, 'funcao' => 'Camera']], $json['sem_candidato']);
    }

    public function test_nao_pode_sem_ia_compara_os_nomes(): void
    {
        $this->som->delete();
        $this->camera->membros()->sync([$this->ana->id, $this->bia->id]);

        $json = $this->sugerir($this->linhas([
            '2026-11-01' => ['restricoes' => 'ana viajando'],
        ]))->json();

        $this->assertFalse($json['ia']);
        $this->assertSame([['linha' => 0, 'ids' => [$this->ana->id]]], $json['nao_podem']);
        $this->assertSame($this->bia->id, $this->grade($json)[0]['Camera']);
    }

    public function test_nao_pode_com_deepseek_entende_apelido_e_ignora_id_inventado(): void
    {
        $this->som->delete();
        $this->camera->membros()->sync([$this->ana->id, $this->bia->id]);
        config(['services.deepseek.api_key' => 'chave-teste']);

        Http::fake(['api.deepseek.com/*' => Http::response(['choices' => [['message' => ['content' => json_encode([
            'linhas' => [['linha' => 0, 'nao_podem' => [$this->bia->id, 99999]]],
        ])]]]])]);

        $json = $this->sugerir($this->linhas([
            '2026-11-01' => ['restricoes' => 'a Bibi tem plantão'],
        ]))->json();

        $this->assertTrue($json['ia']);
        $this->assertSame([['linha' => 0, 'ids' => [$this->bia->id]]], $json['nao_podem']);
        $this->assertSame($this->ana->id, $this->grade($json)[0]['Camera']);

        Http::assertSent(fn ($req) => $req->hasHeader('Authorization', 'Bearer chave-teste')
            && str_contains($req->body(), 'Bibi'));
    }

    public function test_deepseek_fora_do_ar_cai_para_os_nomes(): void
    {
        $this->som->delete();
        config(['services.deepseek.api_key' => 'chave-teste']);
        Http::fake(['api.deepseek.com/*' => Http::response('erro', 500)]);

        $json = $this->sugerir($this->linhas([
            '2026-11-01' => ['restricoes' => 'Caio não pode'],
        ]))->assertOk()->json();

        $this->assertFalse($json['ia']);
        $this->assertSame([['linha' => 0, 'ids' => [$this->caio->id]]], $json['nao_podem']);
    }

    public function test_sem_restricoes_nao_chama_a_ia(): void
    {
        config(['services.deepseek.api_key' => 'chave-teste']);
        Http::fake();

        $this->sugerir($this->linhas())->assertOk();

        Http::assertNothingSent();
    }

    public function test_membro_comum_nao_pode_sugerir(): void
    {
        $this->sugerir($this->linhas(), $this->ana)->assertForbidden();
    }

    public function test_marcar_quem_faz_a_funcao_aceita_so_membros_do_grupo(): void
    {
        $deFora = $this->usuario('Fulano de Fora', 'membro');

        $this->actingAs($this->pastor)
            ->put("/admin/grupos/{$this->grupo->id}/funcoes/{$this->camera->id}/membros", [
                'membros_ids' => [$this->ana->id, $deFora->id],
            ])->assertRedirect();

        $this->assertSame([$this->ana->id], $this->camera->membros()->pluck('users.id')->all());

        $this->actingAs($this->pastor)
            ->get('/admin/planejador?grupo_id='.$this->grupo->id.'&mes=2026-11')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('aptos', ['Camera' => [$this->ana->id]]));
    }

    public function test_quem_sai_do_grupo_sai_das_funcoes(): void
    {
        $this->camera->membros()->sync([$this->ana->id, $this->bia->id]);

        $this->actingAs($this->pastor)->put("/admin/grupos/{$this->grupo->id}", [
            'nome' => 'Mídias',
            'membros_ids' => [$this->bia->id, $this->caio->id],
        ])->assertRedirect();

        $this->assertSame([$this->bia->id], $this->camera->membros()->pluck('users.id')->all());
    }

    public function test_planejador_mostra_o_historico_de_cada_pessoa(): void
    {
        $this->escalar($this->ana, '2026-10-04', 'Camera');
        $this->escalar($this->ana, '2026-10-25', 'Camera');
        $this->escalar($this->ana, '2026-05-01', 'Camera'); // fora dos 90 dias

        $this->actingAs($this->pastor)
            ->get('/admin/planejador?grupo_id='.$this->grupo->id.'&mes=2026-11')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('historico', [$this->ana->id => ['recentes' => 2, 'ultima' => '2026-10-25']]));
    }
}
