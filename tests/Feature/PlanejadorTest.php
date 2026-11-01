<?php

namespace Tests\Feature;

use App\Models\Culto;
use App\Models\Escala;
use App\Models\Evento;
use App\Models\Grupo;
use App\Models\GrupoFuncao;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class PlanejadorTest extends TestCase
{
    use RefreshDatabase;

    private User $pastor;

    private User $natasha;

    private Grupo $grupo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pastor = $this->usuario('Pastor', 'pastor');
        $this->natasha = $this->usuario('Natasha', 'membro');

        $this->grupo = Grupo::create(['nome' => 'Mídias', 'created_by' => $this->pastor->id]);
        foreach (['Camera', 'Gravação/transmissão', 'Instagram'] as $nome) {
            GrupoFuncao::create(['grupo_id' => $this->grupo->id, 'nome' => $nome]);
        }
        $this->grupo->membros()->attach($this->natasha->id);
    }

    private function usuario(string $nome, string $role): User
    {
        return User::create([
            'name' => $nome,
            'email' => strtolower($nome).uniqid().'@teste.com',
            'password' => 'segredo123',
            'role_id' => Role::where('name', $role)->value('id'),
        ]);
    }

    private function salvar(array $cells)
    {
        return $this->actingAs($this->pastor)->post('/admin/planejador', [
            'grupo_id' => $this->grupo->id,
            'rows' => [[
                'escala_id' => Escala::first()?->id,
                'data' => '2026-11-08',
                'culto_id' => null,
                'evento_id' => null,
                'vinculo' => 'Culto de Domingo',
                'restricoes' => '',
                'cells' => $cells,
            ]],
        ]);
    }

    public function test_pessoa_em_duas_funcoes_vira_uma_linha_com_as_duas(): void
    {
        // Ordem invertida de propósito: grava na ordem das colunas do grupo
        $this->salvar(['Instagram' => $this->natasha->id, 'Camera' => $this->natasha->id])
            ->assertSessionHas('success');

        $escala = Escala::with('escalaMembros')->sole();
        $this->assertCount(1, $escala->escalaMembros);
        $this->assertSame('Camera, Instagram', $escala->escalaMembros->first()->funcao);
    }

    public function test_planejador_separa_as_funcoes_de_volta_nas_colunas(): void
    {
        $this->salvar(['Camera' => $this->natasha->id, 'Instagram' => $this->natasha->id]);

        $this->actingAs($this->pastor)
            ->get('/admin/planejador?grupo_id='.$this->grupo->id.'&mes=2026-11')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('rows.0.cells.Camera', $this->natasha->id)
                ->where('rows.0.cells.Instagram', $this->natasha->id)
                ->missing('rows.0.cells.Gravação/transmissão'));
    }

    public function test_tirar_uma_das_funcoes_mantem_a_outra(): void
    {
        $this->salvar(['Camera' => $this->natasha->id, 'Instagram' => $this->natasha->id]);
        $this->salvar(['Instagram' => $this->natasha->id]);

        $this->assertSame('Instagram', Escala::with('escalaMembros')->sole()->escalaMembros->sole()->funcao);
    }

    public function test_funcao_fora_da_lista_do_grupo_continua_inteira(): void
    {
        $this->salvar(['Camera' => $this->natasha->id]);
        Escala::first()->escalaMembros()->update(['funcao' => 'Som, luz']);

        $this->actingAs($this->pastor)
            ->get('/admin/planejador?grupo_id='.$this->grupo->id.'&mes=2026-11')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('rows.0.cells', ['Som, luz' => $this->natasha->id]));
    }

    private function vinculosDoPlanejador(): array
    {
        $vinculos = [];
        $this->actingAs($this->pastor)
            ->get('/admin/planejador?grupo_id='.$this->grupo->id.'&mes=2026-11')
            ->assertInertia(function (AssertableInertia $page) use (&$vinculos) {
                $vinculos = collect($page->toArray()['props']['rows'])->pluck('vinculo')->unique()->values()->all();
            });

        return $vinculos;
    }

    public function test_grupo_escolhe_cultos_e_eventos_do_planejador(): void
    {
        $domingo = Culto::create(['nome' => 'Culto de Domingo', 'dia_semana' => 'Domingo', 'horario' => '19:00', 'ativo' => true]);
        $quarta = Culto::create(['nome' => 'Culto de Oração', 'dia_semana' => 'Quarta', 'horario' => '20:00', 'ativo' => true]);
        Evento::create(['nome' => 'Marcha pra Jesus', 'data_evento' => '2026-11-14', 'horario' => '15h', 'ativo' => true]);

        // Padrão: tudo aparece
        $this->assertEqualsCanonicalizing(['Culto de Domingo', 'Culto de Oração', 'Marcha pra Jesus'], $this->vinculosDoPlanejador());

        // Desmarca a quarta e os eventos no formulário do grupo
        $this->actingAs($this->pastor)->put('/admin/grupos/'.$this->grupo->id, [
            'nome' => 'Mídias',
            'membros_ids' => [$this->natasha->id],
            'cultos_ids' => [$domingo->id],
            'escala_eventos' => false,
        ])->assertSessionHasNoErrors();

        $this->assertSame([$quarta->id], $this->grupo->cultosSemEscala()->pluck('cultos.id')->all());
        $this->assertSame(['Culto de Domingo'], $this->vinculosDoPlanejador());
    }

    public function test_escala_ja_criada_aparece_mesmo_com_culto_desmarcado(): void
    {
        $quarta = Culto::create(['nome' => 'Culto de Oração', 'dia_semana' => 'Quarta', 'horario' => '20:00', 'ativo' => true]);
        Escala::create([
            'titulo' => 'Culto de Oração', 'data' => '2026-11-04', 'hora_inicio' => '20:00', 'hora_fim' => '21:00',
            'grupo_id' => $this->grupo->id, 'culto_id' => $quarta->id, 'status' => 'pendente', 'created_by' => $this->pastor->id,
        ]);
        $this->grupo->cultosSemEscala()->attach($quarta->id);

        $this->assertSame(['Culto de Oração'], $this->vinculosDoPlanejador());
        $this->actingAs($this->pastor)
            ->get('/admin/planejador?grupo_id='.$this->grupo->id.'&mes=2026-11')
            ->assertInertia(fn (AssertableInertia $page) => $page->has('rows', 1));
    }

    public function test_culto_desativado_mantem_a_escolha_ao_salvar_o_grupo(): void
    {
        $antigo = Culto::create(['nome' => 'Culto Antigo', 'dia_semana' => 'Sexta', 'horario' => '20:00', 'ativo' => false]);
        $this->grupo->cultosSemEscala()->attach($antigo->id);

        // Formulário só mostra cultos ativos, então não manda o antigo
        $this->actingAs($this->pastor)->put('/admin/grupos/'.$this->grupo->id, [
            'nome' => 'Mídias', 'membros_ids' => [], 'cultos_ids' => [], 'escala_eventos' => true,
        ])->assertSessionHasNoErrors();

        $this->assertSame([$antigo->id], $this->grupo->cultosSemEscala()->pluck('cultos.id')->all());
    }
}
