<?php

namespace Tests\Feature;

use App\Models\Escala;
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
}
