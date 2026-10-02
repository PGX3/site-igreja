<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CadastroVisitanteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $sobrescreve
     * @return array<string, mixed>
     */
    private function payload(array $sobrescreve = []): array
    {
        return array_merge([
            'name' => 'Joana da Silva',
            'telefone' => '(51) 99282-4071',
            'data_nascimento' => '1992-04-17',
            'como_conheceu' => 'Convite de um amigo',
            'email' => null,
        ], $sobrescreve);
    }

    public function test_formulario_curto_abre_com_as_opcoes_de_como_conheceu(): void
    {
        $this->get('/sou-novo')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Cadastro/Visitante')
                ->where('comoConheceuOpcoes', User::COMO_CONHECEU_OPCOES));
    }

    public function test_cadastra_visitante_com_os_campos_minimos(): void
    {
        Carbon::setTestNow('2026-10-02');

        $this->post('/sou-novo', $this->payload())
            ->assertRedirect('/sou-novo/obrigado');

        $visitante = User::where('name', 'Joana da Silva')->sole();

        $this->assertSame('visitante', $visitante->tipo);
        $this->assertSame('(51) 99282-4071', $visitante->telefone);
        $this->assertSame('Convite de um amigo', $visitante->como_conheceu);
        $this->assertSame('1992-04-17', $visitante->data_nascimento->toDateString());
        $this->assertSame('2026-10-02', $visitante->primeira_visita->toDateString());
        $this->assertNull($visitante->email);

        Carbon::setTestNow();
    }

    public function test_opcao_outro_guarda_o_detalhe_digitado(): void
    {
        $this->post('/sou-novo', $this->payload([
            'como_conheceu' => 'Outro',
            'como_conheceu_detalhe' => '  Ouvi no rádio  ',
        ]))->assertRedirect('/sou-novo/obrigado');

        $this->assertSame('Ouvi no rádio', User::sole()->como_conheceu);
    }

    public function test_outro_sem_detalhe_cai_no_rotulo_generico(): void
    {
        $this->post('/sou-novo', $this->payload(['como_conheceu' => 'Outro']))
            ->assertRedirect('/sou-novo/obrigado');

        $this->assertSame('Outro', User::sole()->como_conheceu);
    }

    public function test_reenvio_do_mesmo_telefone_nao_duplica_e_completa_o_que_falta(): void
    {
        $visitante = User::create([
            'name' => 'Joana da Silva',
            'telefone' => '(51) 99282-4071',
            'tipo' => 'visitante',
            'como_conheceu' => 'Passei em frente',
            'primeira_visita' => '2026-09-01',
        ]);

        // Mesmo número, escrito com código do país e sem pontuação.
        $this->post('/sou-novo', $this->payload([
            'telefone' => '+5551992824071',
            'email' => 'joana@exemplo.com',
            'como_conheceu' => 'Redes sociais',
        ]))->assertRedirect('/sou-novo/obrigado');

        $this->assertSame(1, User::count());

        $visitante->refresh();
        $this->assertSame('joana@exemplo.com', $visitante->email);
        $this->assertSame('1992-04-17', $visitante->data_nascimento->toDateString());
        // Histórico pastoral preservado: nada de sobrescrever o que já havia.
        $this->assertSame('Passei em frente', $visitante->como_conheceu);
        $this->assertSame('2026-09-01', $visitante->primeira_visita->toDateString());
    }

    public function test_membro_com_o_mesmo_telefone_nao_e_duplicado_nem_alterado(): void
    {
        $membro = User::create([
            'name' => 'Pedro Membro',
            'email' => 'pedro@exemplo.com',
            'telefone' => '51 99282-4071',
            'tipo' => 'membro',
        ]);

        $this->post('/sou-novo', $this->payload(['telefone' => '(51) 99282-4071']))
            ->assertRedirect('/sou-novo/obrigado');

        $this->assertSame(1, User::count());

        $membro->refresh();
        $this->assertSame('membro', $membro->tipo);
        $this->assertNull($membro->data_nascimento);
    }

    public function test_telefone_diferente_cria_outro_visitante(): void
    {
        User::create([
            'name' => 'Joana da Silva',
            'telefone' => '(51) 99282-4071',
            'tipo' => 'visitante',
        ]);

        $this->post('/sou-novo', $this->payload(['telefone' => '(51) 98888-1234']))
            ->assertRedirect('/sou-novo/obrigado');

        $this->assertSame(2, User::count());
    }

    public function test_campos_obrigatorios_sao_validados(): void
    {
        $this->post('/sou-novo', [])
            ->assertSessionHasErrors(['name', 'telefone', 'data_nascimento', 'como_conheceu']);

        $this->assertSame(0, User::count());
    }

    public function test_como_conheceu_fora_da_lista_e_recusado(): void
    {
        $this->post('/sou-novo', $this->payload(['como_conheceu' => 'Sei lá']))
            ->assertSessionHasErrors('como_conheceu');

        $this->assertSame(0, User::count());
    }

    public function test_email_de_outra_pessoa_e_recusado(): void
    {
        User::create([
            'name' => 'Outro Alguém',
            'email' => 'joana@exemplo.com',
            'telefone' => '(51) 90000-0000',
            'tipo' => 'membro',
        ]);

        $this->post('/sou-novo', $this->payload(['email' => 'joana@exemplo.com']))
            ->assertSessionHasErrors('email');

        $this->assertSame(1, User::count());
    }

    public function test_seo_indexa_o_formulario_e_esconde_o_obrigado(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(url('/sou-novo'), false);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /sou-novo/obrigado');
    }

    public function test_honeypot_descarta_o_envio_sem_criar_usuario(): void
    {
        $this->post('/sou-novo', $this->payload(['_honey' => 'bot']))
            ->assertRedirect('/sou-novo/obrigado');

        $this->assertSame(0, User::count());
    }
}
