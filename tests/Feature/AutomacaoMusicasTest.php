<?php

namespace Tests\Feature;

use App\Models\Musica;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class AutomacaoMusicasTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'token-de-teste-automacao';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.automacao.token' => self::TOKEN]);
    }

    private function enviar(array $dados)
    {
        return $this->postJson('/api/v1/automacao/musicas', $dados, ['Authorization' => 'Bearer '.self::TOKEN]);
    }

    public function test_exige_token(): void
    {
        $this->postJson('/api/v1/automacao/musicas', ['nome' => 'Hosana'])->assertUnauthorized();
        $this->getJson('/api/v1/automacao/musicas')->assertUnauthorized();
        $this->assertSame(0, Musica::count());
    }

    private const LETRA = '<p>NÃO HÁ OUTRO COMO TU</p><p></p>'
        .'<p><span class="instrucao">REFRÃO:</span></p>'
        .'<p class="refrao">TU ÉS O ALFA E ÔMEGA</p><p class="refrao">INÍCIO E FIM</p><p></p>'
        .'<p><span class="instrucao">(REFRÃO 2X)</span></p><p></p>'
        .'<p><span class="instrucao">6X</span> TUDO PRA MIM</p>';

    public function test_cria_musica_com_tags_e_secoes_do_publico(): void
    {
        $this->enviar([
            'nome' => 'Alfa e Ômega',
            'artista' => 'Marine Friesen',
            'bpm' => 146,
            'categoria' => 'lenta',
            'tom' => 'G#m',
            'letra' => self::LETRA.'<p class="qualquer" onclick="x()">fim</p><script>alert(1)</script>',
            'secoes_publico' => [
                ['tipo' => 'verso', 'texto' => "NÃO HÁ OUTRO COMO TU\r\nSOBERANO E FIEL  "],
                ['tipo' => 'refrao', 'texto' => "<b>TU ÉS O ALFA E ÔMEGA</b>\n\n\nINÍCIO E FIM"],
                ['tipo' => 'refrao', 'texto' => "TU ÉS O ALFA E ÔMEGA\nINÍCIO E FIM"],
                ['texto' => 'TUDO PRA MIM'],
                ['tipo' => 'verso', 'texto' => '   '],
            ],
        ])->assertCreated()
            ->assertJsonPath('acao', 'criada')
            ->assertJsonPath('musica.tem_letra', true)
            ->assertJsonPath('musica.secoes_publico', 4);

        $m = Musica::sole();
        $this->assertSame('Marine Friesen', $m->artista);
        $this->assertSame(146, $m->bpm);
        $this->assertSame('lenta', $m->categoria);
        $this->assertSame('Sol#m', $m->tom);

        // Tags de refrão e instrução ficam. Classe desconhecida, evento e script saem.
        $this->assertStringContainsString('<p class="refrao">TU ÉS O ALFA E ÔMEGA</p>', $m->letra);
        $this->assertStringContainsString('<span class="instrucao">6X</span>', $m->letra);
        $this->assertStringNotContainsString('qualquer', $m->letra);
        $this->assertStringNotContainsString('onclick', $m->letra);
        $this->assertStringNotContainsString('script', $m->letra);

        // Telão: seções em texto puro, sem tipo vira verso, seção vazia some
        $this->assertSame([
            ['tipo' => 'verso', 'texto' => "NÃO HÁ OUTRO COMO TU\nSOBERANO E FIEL"],
            ['tipo' => 'refrao', 'texto' => "TU ÉS O ALFA E ÔMEGA\nINÍCIO E FIM"],
            ['tipo' => 'refrao', 'texto' => "TU ÉS O ALFA E ÔMEGA\nINÍCIO E FIM"],
            ['tipo' => 'verso', 'texto' => 'TUDO PRA MIM'],
        ], $m->secoes_publico);
        $this->assertSame(
            "NÃO HÁ OUTRO COMO TU\nSOBERANO E FIEL\n\nTU ÉS O ALFA E ÔMEGA\nINÍCIO E FIM\n\nTU ÉS O ALFA E ÔMEGA\nINÍCIO E FIM\n\nTUDO PRA MIM",
            $m->letraPublicoTexto(),
        );
    }

    public function test_versao_crua_e_so_o_texto_sem_as_instrucoes(): void
    {
        $m = new Musica(['letra' => self::LETRA]);

        $this->assertSame(
            "NÃO HÁ OUTRO COMO TU\n\nTU ÉS O ALFA E ÔMEGA\nINÍCIO E FIM\n\nTUDO PRA MIM",
            $m->letraCrua(),
        );
        $this->assertSame('', (new Musica(['letra' => '']))->letraCrua());
    }

    public function test_app_recebe_as_tags_com_cor_e_as_versoes_novas(): void
    {
        $this->enviar(['nome' => 'Alfa e Ômega', 'letra' => self::LETRA, 'secoes_publico' => [['tipo' => 'refrao', 'texto' => 'INÍCIO E FIM']]]);
        $membro = User::create([
            'name' => 'Músico', 'email' => 'musico@teste.com', 'password' => 'segredo123',
            'role_id' => Role::where('name', 'membro')->value('id'),
        ]);

        $resposta = $this->actingAs($membro, 'sanctum')->getJson('/api/v1/musicas/'.Musica::sole()->id)->assertOk();

        $this->assertStringContainsString('<p class="refrao" style="color:#16a34a">INÍCIO E FIM</p>', $resposta->json('data.letra'));
        $this->assertStringContainsString('<span class="instrucao" style="color:#dc2626">6X</span>', $resposta->json('data.letra'));
        $this->assertStringNotContainsString('REFRÃO', $resposta->json('data.letra_crua'));
        $resposta->assertJsonPath('data.secoes_publico', [['tipo' => 'refrao', 'texto' => 'INÍCIO E FIM']]);
    }

    public function test_reenviar_atualiza_em_vez_de_duplicar(): void
    {
        $this->enviar(['nome' => 'Hosana', 'artista' => 'Nívea Soares', 'letra' => '<p>versão 1</p>', 'bpm' => 120])
            ->assertCreated();

        // Caixa, acento e espaços diferentes: é a mesma música. Campo ausente (bpm) fica como estava.
        $this->enviar(['nome' => ' HOSANA ', 'artista' => 'nivea  soares', 'letra' => '<p>versão 2</p>'])
            ->assertOk()
            ->assertJsonPath('acao', 'atualizada');

        $m = Musica::sole();
        $this->assertStringContainsString('versão 2', $m->letra);
        $this->assertSame(120, $m->bpm);
    }

    public function test_mesmo_nome_com_artistas_diferentes_sao_musicas_diferentes(): void
    {
        $this->enviar(['nome' => 'Hosana', 'artista' => 'Nívea Soares'])->assertCreated();
        $this->enviar(['nome' => 'Hosana', 'artista' => 'Além dos Cravos'])->assertCreated();
        $this->enviar(['nome' => 'Hosana', 'artista' => 'Mariana Valadão (Hillsong)'])->assertCreated();

        $this->assertSame(3, Musica::count());
    }

    public function test_musica_cadastrada_a_mao_sem_artista_e_aproveitada(): void
    {
        $existente = Musica::create(['nome' => 'Oceanos', 'letra' => '', 'tom' => 'Ré']);

        $this->enviar(['nome' => 'OCEANOS', 'artista' => 'Ana Nóbrega', 'secoes_publico' => [['texto' => 'Tua voz me chama']]])
            ->assertOk()
            ->assertJsonPath('musica.id', $existente->id);

        $m = Musica::sole();
        $this->assertSame('Oceanos', $m->nome);
        $this->assertSame('Ana Nóbrega', $m->artista);
        $this->assertSame('Ré', $m->tom);
    }

    public function test_valida_categoria_nome_e_tipo_de_secao(): void
    {
        $this->enviar(['nome' => 'Graça', 'secoes_publico' => [['tipo' => 'coro', 'texto' => 'x']]])
            ->assertStatus(422)->assertJsonValidationErrors('secoes_publico.0.tipo');
        $this->enviar(['artista' => 'Sem nome'])->assertStatus(422)->assertJsonValidationErrors('nome');
        $this->enviar(['nome' => 'Graça', 'categoria' => 'agitada'])->assertStatus(422)->assertJsonValidationErrors('categoria');
    }

    public function test_lista_o_que_ja_existe_sem_as_letras(): void
    {
        $this->enviar(['nome' => 'Graça', 'artista' => 'Israel Salazar', 'letra' => '<p>letra</p>']);

        $this->getJson('/api/v1/automacao/musicas', ['Authorization' => 'Bearer '.self::TOKEN])
            ->assertOk()
            ->assertJsonPath('data.0.nome', 'Graça')
            ->assertJsonPath('data.0.tem_letra', true)
            ->assertJsonPath('data.0.secoes_publico', 0)
            ->assertJsonMissingPath('data.0.letra');
    }

    public function test_painel_salva_e_mostra_os_campos_novos(): void
    {
        $pastor = User::create([
            'name' => 'Pastor',
            'email' => 'pastor'.uniqid().'@teste.com',
            'password' => 'segredo123',
            'role_id' => Role::where('name', 'pastor')->value('id'),
        ]);

        $this->actingAs($pastor)->post('/admin/musicas', [
            'nome' => 'Digno', 'artista' => 'Elevation Worship', 'bpm' => 140, 'categoria' => 'lenta',
            'letra' => '<p>letra <span class="instrucao">2X</span></p>',
            'secoes_publico' => [['tipo' => 'verso', 'texto' => 'Estrofe 1'], ['tipo' => 'refrao', 'texto' => 'Estrofe 2']],
        ])->assertSessionHasNoErrors();

        $m = Musica::sole();

        $this->actingAs($pastor)->get("/admin/musicas/{$m->id}")
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('musica.artista', 'Elevation Worship')
                ->where('musica.bpm', 140)
                ->where('musica.categoria', 'Lenta')
                ->where('musica.letra_crua', 'letra')
                ->where('musica.secoes_publico.1.rotulo', 'Refrão')
                ->where('musica.letra_publico', "Estrofe 1\n\nEstrofe 2"));
    }
}
