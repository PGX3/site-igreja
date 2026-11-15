<?php

namespace Tests\Feature;

use App\Models\Pregacao;
use App\Models\PregacaoCorte;
use App\Models\Role;
use App\Models\User;
use App\Services\YouTubePregacoesImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PregacaoCortesTest extends TestCase
{
    use RefreshDatabase;

    private const CANAL = 'UCabc123';

    private function usuario(string $papel, bool $pregador = false): User
    {
        $user = User::create([
            'name' => 'Fulano',
            'email' => $papel.uniqid().'@teste.local',
            'password' => 'segredo123',
            'role_id' => Role::where('name', $papel)->value('id'),
            'tipo' => 'membro',
        ]);
        $user->forceFill(['is_pregador' => $pregador])->save();

        return $user;
    }

    private function pregacao(string $titulo, string $data, string $video): Pregacao
    {
        return Pregacao::create([
            'titulo' => $titulo, 'data_pregacao' => $data, 'ativo' => true,
            'youtube_url' => "https://www.youtube.com/watch?v={$video}",
        ]);
    }

    /** @param  array<int, array{0: string, 1: string, 2: string}>  $videos  [id, título, publicado] */
    private function feed(array $videos): string
    {
        $entradas = collect($videos)->map(fn ($v) => '<entry><yt:videoId>'.$v[0].'</yt:videoId><title>'.htmlspecialchars($v[1]).'</title>'
            .'<published>'.$v[2].'</published><media:group><media:description>desc '.$v[0].'</media:description></media:group></entry>')->implode('');

        return '<?xml version="1.0"?><feed xmlns="http://www.w3.org/2005/Atom" xmlns:yt="http://www.youtube.com/xml/schemas/2015" '
            .'xmlns:media="http://search.yahoo.com/mrss/">'.$entradas.'</feed>';
    }

    /** Listas que o YouTube "tem" agora (dá para trocar no meio do teste). */
    private array $listas = [];

    private function fakeYoutube(array $listas, array $shortsHead = []): void
    {
        $this->listas = $listas;
        Http::fake(function ($request) use ($shortsHead) {
            $url = $request->url();
            if (str_contains($url, '/shorts/')) {
                $id = basename(parse_url($url, PHP_URL_PATH));

                return Http::response('', in_array($id, $shortsHead, true) ? 200 : 303);
            }
            parse_str((string) parse_url($url, PHP_URL_QUERY), $q);
            $lista = $this->listas[$q['playlist_id'] ?? ''] ?? null;

            return $lista === null ? Http::response('', 404) : Http::response($this->feed($lista), 200);
        });
    }

    // ─── Importação ───

    public function test_shorts_viram_cortes_dentro_da_pregacao_certa(): void
    {
        config(['services.youtube.api_key' => null]);
        $rei = $this->pregacao('Queremos um rei - Luciano Cabral - 04/10/2026', '2026-10-04', 'reiREIrei01');

        $this->fakeYoutube([
            'UULFabc123' => [['grACAfeObra', 'Graça, fé e obras - Leandro Silva - 11/10/2026', '2026-10-12T22:14:21+00:00']],
            'UUSHabc123' => [
                ['shortGraca1', 'Como Deus pode declarar um culpado inocente? #Shorts', '2026-10-13T13:40:21+00:00'],
                ['shortRei001', 'O Rei está aqui | Queremos um rei #Shorts', '2026-10-13T13:41:00+00:00'],
            ],
        ]);

        $resultado = app(YouTubePregacoesImporter::class)->importar(self::CANAL);

        $this->assertSame(['pregacoes' => 1, 'cortes' => 2], $resultado);
        $graca = Pregacao::where('titulo', 'like', 'Graça%')->firstOrFail();
        $this->assertSame('2026-10-11', $graca->data_pregacao->toDateString(), 'data do culto vem do título');
        $this->assertSame(2, Pregacao::count(), 'Short não vira pregação');

        $this->assertSame($graca->id, PregacaoCorte::where('youtube_id', 'shortGraca1')->value('pregacao_id'));
        $this->assertSame($rei->id, PregacaoCorte::where('youtube_id', 'shortRei001')->value('pregacao_id'), 'tema depois da barra manda');
        $this->assertSame('O Rei está aqui | Queremos um rei', PregacaoCorte::where('youtube_id', 'shortRei001')->value('titulo'));

        // Rodar de novo não duplica nada.
        $this->assertSame(['pregacoes' => 0, 'cortes' => 0], app(YouTubePregacoesImporter::class)->importar(self::CANAL));
    }

    public function test_pregacao_que_chega_depois_puxa_os_cortes_dela(): void
    {
        config(['services.youtube.api_key' => null]);
        $antiga = $this->pregacao('Providência', '2026-09-27', 'provProv001');
        $this->fakeYoutube([
            'UULFabc123' => [['provProv001', 'Providência', '2026-09-28T10:00:00+00:00']],
            'UUSHabc123' => [['shortGraca1', 'O que é fé? #Shorts', '2026-10-17T10:00:00+00:00']],
        ]);
        app(YouTubePregacoesImporter::class)->importar(self::CANAL);
        $this->assertSame($antiga->id, PregacaoCorte::value('pregacao_id'));

        // A pregação do dia 11 só entra depois (ex.: estava fora do feed).
        $this->listas = ([
            'UULFabc123' => [['grACAfeObra', 'Graça, fé e obras - Leandro Silva - 11/10/2026', '2026-10-12T22:00:00+00:00']],
            'UUSHabc123' => [],
        ]);
        app(YouTubePregacoesImporter::class)->importar(self::CANAL);

        $this->assertSame(Pregacao::where('youtube_url', 'like', '%grACAfeObra')->value('id'), PregacaoCorte::value('pregacao_id'));
    }

    public function test_sem_listas_separadas_confere_cada_video_no_link_de_shorts(): void
    {
        config(['services.youtube.api_key' => null]);
        $this->fakeYoutube([
            'UUabc123' => [
                ['culto000001', 'Nova chance - Felipe Leites - 25/10/2026', '2026-10-27T02:38:29+00:00'],
                ['vertical001', 'Quem podia atirar a primeira pedra?', '2026-10-28T15:00:00+00:00'],
            ],
        ], shortsHead: ['vertical001']);

        $resultado = app(YouTubePregacoesImporter::class)->importar(self::CANAL);

        $this->assertSame(['pregacoes' => 1, 'cortes' => 1], $resultado);
        $this->assertSame(Pregacao::value('id'), PregacaoCorte::value('pregacao_id'));
    }

    public function test_corte_movido_a_mao_nao_e_mexido_pelo_importador(): void
    {
        $a = $this->pregacao('A', '2026-10-04', 'aaaaaaaaaaa');
        $b = $this->pregacao('B', '2026-10-11', 'bbbbbbbbbbb');
        $corte = PregacaoCorte::create(['youtube_id' => 'ccccccccccc', 'titulo' => 'Corte', 'publicado_em' => '2026-10-13 10:00:00', 'pregacao_id' => $b->id]);

        $this->actingAs($this->usuario('pastor'))
            ->patchJson("/admin/cortes/{$corte->id}", ['pregacao_id' => $a->id])->assertOk();

        PregacaoCorte::revincular();
        $corte->refresh();
        $this->assertSame($a->id, $corte->pregacao_id);
        $this->assertTrue($corte->fixado);
    }

    // ─── Painel ───

    public function test_so_pastor_mexe_nos_cortes(): void
    {
        $preg = $this->pregacao('A', '2026-10-04', 'aaaaaaaaaaa');
        $corte = PregacaoCorte::create(['youtube_id' => 'ccccccccccc', 'titulo' => 'Corte', 'publicado_em' => now(), 'pregacao_id' => $preg->id]);
        $pregador = $this->usuario('membro', true);

        $this->actingAs($pregador)->patchJson("/admin/cortes/{$corte->id}", ['ativo' => false])->assertForbidden();
        $this->actingAs($pregador)->postJson("/admin/pregacoes/{$preg->id}/cortes", ['url' => 'https://youtube.com/shorts/ddddddddddd'])->assertForbidden();
    }

    public function test_pastor_adiciona_corte_por_link_e_esconde(): void
    {
        Http::fake(['www.youtube.com/oembed*' => Http::response(['title' => 'Foi só coincidência? #Shorts'])]);
        $preg = $this->pregacao('A', '2026-10-04', 'aaaaaaaaaaa');
        $pastor = $this->usuario('pastor');

        $this->actingAs($pastor)->postJson("/admin/pregacoes/{$preg->id}/cortes", ['url' => 'https://youtube.com/shorts/aaaaaaaaaaa'])
            ->assertStatus(422)->assertJsonPath('errors.url.0', 'Esse vídeo é uma pregação, não um corte.');

        $this->actingAs($pastor)->postJson("/admin/pregacoes/{$preg->id}/cortes", ['url' => 'https://youtube.com/shorts/ddddddddddd?si=x'])
            ->assertOk()->assertJsonPath('cortes.0.titulo', 'Foi só coincidência?');

        $corte = PregacaoCorte::where('youtube_id', 'ddddddddddd')->firstOrFail();
        $this->assertTrue($corte->fixado);

        $this->actingAs($pastor)->get("/admin/pregacoes/{$preg->id}/edit")
            ->assertInertia(fn ($p) => $p->has('cortes', 1)->has('pregacoesParaCortes', 1));

        $this->actingAs($pastor)->patchJson("/admin/cortes/{$corte->id}", ['ativo' => false])->assertOk();
        $this->get("/pregacoes/{$preg->id}")->assertInertia(fn ($p) => $p->has('pregacao.cortes', 0));
    }

    // ─── Site e API ───

    public function test_site_e_api_mostram_os_cortes_dentro_da_pregacao(): void
    {
        $preg = $this->pregacao('A', '2026-10-04', 'aaaaaaaaaaa');
        PregacaoCorte::create(['youtube_id' => 'segundo0002', 'titulo' => 'Dois', 'publicado_em' => '2026-10-07 10:00:00', 'pregacao_id' => $preg->id]);
        PregacaoCorte::create(['youtube_id' => 'primeiro001', 'titulo' => 'Um', 'publicado_em' => '2026-10-06 10:00:00', 'pregacao_id' => $preg->id]);
        PregacaoCorte::create(['youtube_id' => 'escondido01', 'titulo' => 'X', 'publicado_em' => '2026-10-06 10:00:00', 'pregacao_id' => $preg->id, 'ativo' => false]);

        $this->get("/pregacoes/{$preg->id}")->assertInertia(fn ($p) => $p->component('PregacaoDetalhe')
            ->has('pregacao.cortes', 2)
            ->where('pregacao.cortes.0.titulo', 'Um')
            ->where('pregacao.cortes.0.url', 'https://www.youtube.com/shorts/primeiro001'));

        $this->get('/pregacoes')->assertInertia(fn ($p) => $p->has('pregacoes', 1));

        $this->actingAs($this->usuario('membro'), 'sanctum')->getJson("/api/v1/pregacoes/{$preg->id}")->assertOk()->assertJsonCount(2, 'data.cortes');
    }

    // ─── Migração dos Shorts antigos ───

    public function test_migracao_transforma_shorts_cadastrados_como_pregacao_em_cortes(): void
    {
        $migracao = require database_path('migrations/2026_11_15_000001_create_pregacao_cortes_table.php');
        $migracao->down();

        $sermao = $this->pregacao('Onde você está seguro? - Luciano Cabral - 01/11/2026', '2026-11-03', '5h11hteKvqk');
        $this->pregacao('Você moraria num bunker? #Shorts', '2026-11-05', 'Xo1Bjnnb_5c');
        $this->pregacao('Foi só coincidência? #Shorts', '2026-10-22', 'AjHlKtVHbr8'); // antes do sermão: fica sem pregação
        $comRoteiro = $this->pregacao('Roteiro #shorts', '2026-10-01', 'roteiro0001');
        $comRoteiro->update(['roteiro' => '<p>não mexer</p>', 'ativo' => false]);

        $migracao->up();

        $this->assertTrue(Schema::hasTable('pregacao_cortes'));
        $this->assertSame(2, Pregacao::count(), 'sobram o sermão e a que tem roteiro');
        $this->assertSame(2, DB::table('pregacao_cortes')->count());
        $this->assertSame($sermao->id, PregacaoCorte::where('youtube_id', 'Xo1Bjnnb_5c')->value('pregacao_id'));
        $this->assertSame('Você moraria num bunker?', PregacaoCorte::where('youtube_id', 'Xo1Bjnnb_5c')->value('titulo'));
        $this->assertNull(PregacaoCorte::where('youtube_id', 'AjHlKtVHbr8')->value('pregacao_id'));
    }
}
