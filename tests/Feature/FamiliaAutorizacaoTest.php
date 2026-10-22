<?php

namespace Tests\Feature;

use App\Models\Familia;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Cadastro de famílias não pode servir de atalho para alterar dados de quem já está cadastrado. */
class FamiliaAutorizacaoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function usuario(string $nome, string $role = 'membro', array $extra = []): User
    {
        return User::create(array_merge([
            'name' => $nome,
            'email' => str()->slug($nome).'@exemplo.com',
            'tipo' => 'membro',
            'role_id' => Role::where('name', $role)->value('id'),
        ], $extra));
    }

    private function pessoa(array $sobrescreve = []): array
    {
        return array_merge([
            'id' => null,
            'name' => 'Pessoa Nova',
            'telefone' => '(51) 99999-0000',
            'email' => null,
            'cpf' => null,
            'data_nascimento' => '1990-01-01',
            'tipo' => 'membro',
            'batizado_aguas' => true,
            'is_responsavel' => false,
        ], $sobrescreve);
    }

    private function payload(array $pessoas): array
    {
        return [
            'endereco' => 'Rua das Flores, 10',
            'cidade' => 'Charqueadas',
            'uf' => 'RS',
            'cep' => '96745-000',
            'pessoas' => $pessoas,
        ];
    }

    private function familia(): Familia
    {
        return Familia::create([
            'endereco' => 'Rua A, 1',
            'cidade' => 'Charqueadas',
            'uf' => 'RS',
            'cep' => '96745-000',
        ]);
    }

    public function test_lider_nao_vincula_nem_altera_pastor_ao_criar_familia(): void
    {
        $pastor = $this->usuario('Pastor', 'pastor');
        $lider = $this->usuario('Lider', 'lider');

        $this->actingAs($lider)
            ->post('/admin/familias', $this->payload([
                $this->pessoa(['is_responsavel' => true]),
                $this->pessoa(['id' => $pastor->id, 'name' => 'Pastor', 'email' => 'atacante@exemplo.com', 'cpf' => '52998224725']),
            ]))
            ->assertSessionHasErrors('pessoas.1.id');

        $pastor->refresh();
        $this->assertSame('pastor@exemplo.com', $pastor->email);
        $this->assertNull($pastor->cpf);
        $this->assertNull($pastor->familia_id);
        $this->assertSame(0, Familia::count());
    }

    public function test_lider_nao_vincula_outro_lider_nem_superadmin(): void
    {
        $lider = $this->usuario('Lider', 'lider');
        $outroLider = $this->usuario('Outro Lider', 'lider');
        $superadmin = $this->usuario('Super', 'membro', ['is_superadmin' => true]);

        $this->actingAs($lider)
            ->post('/admin/familias', $this->payload([
                $this->pessoa(['id' => $outroLider->id, 'name' => 'Outro Lider', 'email' => 'x@exemplo.com', 'is_responsavel' => true]),
                $this->pessoa(['id' => $superadmin->id, 'name' => 'Super', 'email' => 'y@exemplo.com']),
            ]))
            ->assertSessionHasErrors(['pessoas.0.id', 'pessoas.1.id']);

        $this->assertSame('outro-lider@exemplo.com', $outroLider->fresh()->email);
        $this->assertSame('super@exemplo.com', $superadmin->fresh()->email);
    }

    public function test_lider_nao_vincula_pastor_ao_editar_familia(): void
    {
        $pastor = $this->usuario('Pastor', 'pastor');
        $lider = $this->usuario('Lider', 'lider');
        $familia = $this->familia();
        $responsavel = $this->usuario('Responsavel', 'membro', ['familia_id' => $familia->id]);

        $this->actingAs($lider)
            ->put("/admin/familias/{$familia->id}", $this->payload([
                $this->pessoa(['id' => $responsavel->id, 'name' => 'Responsavel', 'is_responsavel' => true]),
                $this->pessoa(['id' => $pastor->id, 'name' => 'Pastor', 'email' => 'atacante@exemplo.com']),
            ]))
            ->assertSessionHasErrors('pessoas.1.id');

        $pastor->refresh();
        $this->assertSame('pastor@exemplo.com', $pastor->email);
        $this->assertNull($pastor->familia_id);
    }

    public function test_lider_editando_familia_de_lider_mantem_o_vinculo_sem_alterar_dados(): void
    {
        $lider = $this->usuario('Lider', 'lider');
        $familia = $this->familia();
        $outroLider = $this->usuario('Outro Lider', 'lider', ['familia_id' => $familia->id, 'telefone' => '(51) 1111-1111']);

        $this->actingAs($lider)
            ->put("/admin/familias/{$familia->id}", $this->payload([
                $this->pessoa(['id' => $outroLider->id, 'name' => 'Nome Trocado', 'telefone' => '(51) 2222-2222', 'is_responsavel' => true]),
            ]))
            ->assertRedirect("/admin/familias/{$familia->id}");

        $outroLider->refresh();
        $this->assertSame($familia->id, $outroLider->familia_id);
        $this->assertSame('Outro Lider', $outroLider->name);
        $this->assertSame('(51) 1111-1111', $outroLider->telefone);
        $this->assertSame($outroLider->id, $familia->fresh()->responsavel_id);
    }

    public function test_vincular_membro_avulso_nao_altera_email_cpf_nem_tipo(): void
    {
        $lider = $this->usuario('Lider', 'lider');
        $avulso = $this->usuario('Avulso', 'membro', ['tipo' => 'visitante', 'cpf' => '52998224725']);

        $this->actingAs($lider)
            ->post('/admin/familias', $this->payload([
                $this->pessoa([
                    'id' => $avulso->id,
                    'name' => 'Avulso da Silva',
                    'email' => 'novo@exemplo.com',
                    'cpf' => '11144477735',
                    'tipo' => 'membro',
                    'is_responsavel' => true,
                ]),
            ]))
            ->assertSessionHasNoErrors();

        $avulso->refresh();
        $this->assertNotNull($avulso->familia_id);
        $this->assertSame('Avulso da Silva', $avulso->name);
        $this->assertSame('avulso@exemplo.com', $avulso->email);
        $this->assertSame('52998224725', $avulso->cpf);
        $this->assertSame('visitante', $avulso->tipo);
    }

    public function test_lider_cadastra_pessoa_nova_normalmente(): void
    {
        $lider = $this->usuario('Lider', 'lider');

        $this->actingAs($lider)
            ->post('/admin/familias', $this->payload([
                $this->pessoa(['name' => 'Maria Nova', 'email' => 'maria@exemplo.com', 'cpf' => '52998224725', 'is_responsavel' => true]),
            ]))
            ->assertSessionHasNoErrors();

        $maria = User::where('name', 'Maria Nova')->sole();
        $this->assertSame('maria@exemplo.com', $maria->email);
        $this->assertSame('52998224725', $maria->cpf);
        $this->assertSame($maria->id, Familia::sole()->responsavel_id);
    }

    public function test_pessoa_nova_com_email_ja_usado_e_recusada(): void
    {
        $lider = $this->usuario('Lider', 'lider');
        $this->usuario('Pastor', 'pastor');

        $this->actingAs($lider)
            ->post('/admin/familias', $this->payload([
                $this->pessoa(['email' => 'pastor@exemplo.com', 'is_responsavel' => true]),
            ]))
            ->assertSessionHasErrors('pessoas.0.email');
    }

    public function test_pessoas_disponiveis_para_lider_sem_cpf_e_sem_pastores_lideres(): void
    {
        $lider = $this->usuario('Lider', 'lider');
        $this->usuario('Pastor', 'pastor');
        $this->usuario('Outro Lider', 'lider');
        $this->usuario('Super', 'membro', ['is_superadmin' => true]);
        $this->usuario('Avulso', 'membro', ['cpf' => '52998224725']);

        $this->actingAs($lider)
            ->get('/admin/familias/create')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Familias/Form')
                ->has('pessoas_disponiveis', 1)
                ->where('pessoas_disponiveis.0.name', 'Avulso')
                ->where('pessoas_disponiveis.0.cpf', null));
    }

    public function test_pessoas_disponiveis_para_pastor_trazem_cpf(): void
    {
        $pastor = $this->usuario('Pastor', 'pastor', ['familia_id' => $this->familia()->id]);
        $this->usuario('Lider', 'lider');
        $this->usuario('Avulso', 'membro', ['cpf' => '52998224725']);

        $this->actingAs($pastor)
            ->get('/admin/familias/create')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('pessoas_disponiveis', 2)
                ->where('pessoas_disponiveis.0.name', 'Avulso')
                ->where('pessoas_disponiveis.0.cpf', '52998224725')
                ->where('pessoas_disponiveis.1.name', 'Lider'));
    }

    public function test_pastor_pode_vincular_lider_sem_trocar_email(): void
    {
        $pastor = $this->usuario('Pastor', 'pastor');
        $lider = $this->usuario('Lider', 'lider');

        $this->actingAs($pastor)
            ->post('/admin/familias', $this->payload([
                $this->pessoa(['id' => $lider->id, 'name' => 'Lider', 'email' => 'trocado@exemplo.com', 'is_responsavel' => true]),
            ]))
            ->assertSessionHasNoErrors();

        $lider->refresh();
        $this->assertNotNull($lider->familia_id);
        $this->assertSame('lider@exemplo.com', $lider->email);
    }

    public function test_edicao_esconde_cpf_dos_integrantes_para_lider(): void
    {
        $lider = $this->usuario('Lider', 'lider');
        $familia = $this->familia();
        $this->usuario('Integrante', 'membro', ['familia_id' => $familia->id, 'cpf' => '52998224725']);

        $this->actingAs($lider)
            ->get("/admin/familias/{$familia->id}/edit")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('familia.pessoas.0.name', 'Integrante')
                ->where('familia.pessoas.0.cpf', null));
    }
}
