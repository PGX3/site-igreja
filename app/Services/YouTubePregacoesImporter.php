<?php

namespace App\Services;

use App\Models\Pregacao;
use App\Models\PregacaoCorte;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Importa os vídeos do canal. Vídeo normal vira pregação; Short vira corte
 * dentro da pregação dele (nunca uma pregação nova).
 *
 * O YouTube separa os uploads em playlists automáticas: UULF (vídeos),
 * UULV (lives) e UUSH (Shorts). Se o canal não tiver essas listas, cai para
 * os uploads (UU) e confere cada vídeo novo no link /shorts/.
 */
class YouTubePregacoesImporter
{
    /** @return array{pregacoes: int, cortes: int} */
    public function importar(string $channelId): array
    {
        $sufixo = Str::startsWith($channelId, 'UC') ? substr($channelId, 2) : null;
        if (! $sufixo) {
            throw new RuntimeException('O ID do canal do YouTube deve começar com "UC".');
        }

        $videos = $this->listar("UULF{$sufixo}");
        $lives = $this->listar("UULV{$sufixo}");
        $shorts = $this->listar("UUSH{$sufixo}");

        if ($videos === null && $shorts === null) {
            // Sem as listas separadas: usa todos os uploads e separa os Shorts um a um.
            $uploads = $this->listar("UU{$sufixo}");
            if ($uploads === null) {
                throw new RuntimeException('Não foi possível acessar o canal do YouTube configurado.');
            }
            $conhecidos = $this->idsConhecidos();
            $videos = $shorts = [];
            foreach ($uploads as $v) {
                if (isset($conhecidos[$v['id']])) {
                    continue;
                }
                if ($this->ehShort($v['id'], $v['titulo'])) {
                    $shorts[] = $v;
                } else {
                    $videos[] = $v;
                }
            }
        }

        $novasPregacoes = $this->importarPregacoes([...($videos ?? []), ...($lives ?? [])]);
        $novosCortes = $this->importarCortes($shorts ?? []);

        // Pregação nova pode ser a "dona" de cortes que já estavam ligados a outra.
        PregacaoCorte::revincular();

        return ['pregacoes' => $novasPregacoes, 'cortes' => $novosCortes];
    }

    private function importarPregacoes(array $videos): int
    {
        $conhecidos = $this->idsConhecidos();
        $novas = 0;

        // Do mais antigo para o mais novo: o rascunho da semana pega o vídeo certo.
        foreach (collect($videos)->sortBy('publicado') as $v) {
            if (isset($conhecidos[$v['id']])) {
                continue;
            }
            $this->criarPregacao($v['id'], $v['titulo'], $v['publicado'], $v['descricao']);
            $conhecidos[$v['id']] = true;
            $novas++;
        }

        return $novas;
    }

    private function importarCortes(array $shorts): int
    {
        $conhecidos = $this->idsConhecidos();
        $novos = 0;

        foreach ($shorts as $v) {
            if (isset($conhecidos[$v['id']])) {
                continue;
            }
            PregacaoCorte::create([
                'youtube_id' => $v['id'],
                'titulo' => PregacaoCorte::limparTitulo($v['titulo']),
                'publicado_em' => Carbon::parse($v['publicado']),
            ]);
            $conhecidos[$v['id']] = true;
            $novos++;
        }

        return $novos;
    }

    /** Vídeos já no sistema (pregações e cortes), para não importar duas vezes. */
    private function idsConhecidos(): array
    {
        return Pregacao::query()
            ->pluck('youtube_url')
            ->map(fn ($url) => $this->videoId((string) $url))
            ->filter()
            ->merge(PregacaoCorte::query()->pluck('youtube_id'))
            ->flip()
            ->all();
    }

    /**
     * Vídeos de uma playlist: [id, titulo, publicado, descricao].
     * null = a playlist não existe (o YouTube responde 404 também quando ela está vazia).
     */
    private function listar(string $playlistId): ?array
    {
        return config('services.youtube.api_key')
            ? $this->listarPelaApi($playlistId)
            : $this->listarPeloFeed($playlistId);
    }

    private function listarPelaApi(string $playlistId): ?array
    {
        $videos = [];
        $pageToken = null;

        do {
            $pagina = $this->buscar('playlistItems', array_filter([
                'part' => 'snippet',
                'playlistId' => $playlistId,
                'maxResults' => 50,
                'pageToken' => $pageToken,
            ]));
            if ($pagina === null) {
                return null;
            }

            foreach ($pagina['items'] ?? [] as $item) {
                $id = data_get($item, 'snippet.resourceId.videoId');
                $titulo = trim((string) data_get($item, 'snippet.title'));
                $publicado = data_get($item, 'snippet.publishedAt');
                if ($id && $titulo && $publicado) {
                    $videos[] = ['id' => $id, 'titulo' => $titulo, 'publicado' => $publicado, 'descricao' => data_get($item, 'snippet.description') ?: null];
                }
            }

            $pageToken = $pagina['nextPageToken'] ?? null;
        } while ($pageToken);

        return $videos;
    }

    /** O feed público não exige chave, mas traz só os 15 vídeos mais recentes de cada lista. */
    private function listarPeloFeed(string $playlistId): ?array
    {
        try {
            $resposta = Http::timeout(20)->get('https://www.youtube.com/feeds/videos.xml', ['playlist_id' => $playlistId]);
        } catch (ConnectionException) {
            throw new RuntimeException('Não foi possível conectar ao YouTube. Tente novamente em alguns instantes.');
        }

        if ($resposta->status() === 404) {
            return null;
        }
        if ($resposta->failed()) {
            throw new RuntimeException('Não foi possível acessar o canal do YouTube configurado.');
        }

        libxml_use_internal_errors(true);
        $feed = simplexml_load_string($resposta->body());
        libxml_clear_errors();

        if ($feed === false) { // SimpleXMLElement sem filhos também é "falso"
            throw new RuntimeException('O YouTube não retornou um feed válido para este canal.');
        }

        $namespaces = $feed->getNamespaces(true);
        $yt = $namespaces['yt'] ?? null;
        $media = $namespaces['media'] ?? null;
        if (! $yt) {
            return [];
        }

        $videos = [];
        foreach ($feed->entry ?? [] as $entry) {
            $id = (string) ($entry->children($yt)->videoId ?? '');
            if (! $id) {
                continue;
            }
            $descricao = $media ? trim((string) $entry->children($media)?->group?->description) : '';
            $videos[] = [
                'id' => $id,
                'titulo' => trim((string) $entry->title),
                'publicado' => (string) $entry->published,
                'descricao' => $descricao !== '' ? $descricao : null,
            ];
        }

        return $videos;
    }

    /** O link /shorts/ abre direto (200) se for Short; vídeo normal redireciona para /watch. */
    private function ehShort(string $videoId, string $titulo): bool
    {
        if (Str::contains(Str::lower($titulo), '#shorts')) {
            return true;
        }

        try {
            return Http::timeout(10)->withoutRedirecting()
                ->head("https://www.youtube.com/shorts/{$videoId}")
                ->status() === 200;
        } catch (ConnectionException) {
            return false;
        }
    }

    private function criarPregacao(string $videoId, string $titulo, string $publicadoEm, ?string $descricao): void
    {
        $url = "https://www.youtube.com/watch?v={$videoId}";
        $publicado = Carbon::parse($publicadoEm);

        // Se o pregador já escreveu o roteiro dessa pregação (rascunho sem vídeo na semana
        // anterior à publicação), o vídeo entra nela em vez de criar outra pregação.
        $rascunho = Pregacao::query()
            ->where(fn ($q) => $q->whereNull('youtube_url')->orWhere('youtube_url', ''))
            ->whereBetween('data_pregacao', [$publicado->copy()->subDays(6)->toDateString(), $publicado->toDateString()])
            ->orderByDesc('data_pregacao')
            ->first();

        if ($rascunho) {
            $rascunho->update([
                'youtube_url' => $url,
                'descricao' => $rascunho->descricao ?: ($descricao ?: null),
                'ativo' => true,
            ]);

            return;
        }

        Pregacao::create([
            'titulo' => Str::limit(trim($titulo), 150, ''),
            'youtube_url' => $url,
            'descricao' => $descricao ?: null,
            'data_pregacao' => $this->dataDoCulto($titulo, $publicado)->toDateString(),
            'ativo' => true,
        ]);
    }

    /** "Nova chance - Felipe Leites - 25/10/2026" → 25/10 (dia do culto); senão, o dia em que saiu no YouTube. */
    private function dataDoCulto(string $titulo, Carbon $publicado): Carbon
    {
        if (preg_match('/(\d{2})\/(\d{2})\/(\d{4})\s*$/', trim($titulo), $m) && checkdate((int) $m[2], (int) $m[1], (int) $m[3])) {
            $data = Carbon::create((int) $m[3], (int) $m[2], (int) $m[1]);
            if ($data->lte($publicado) && $data->gte($publicado->copy()->subDays(30))) {
                return $data;
            }
        }

        return $publicado;
    }

    /** null = não encontrado (playlist que o canal não tem). */
    private function buscar(string $recurso, array $parametros): ?array
    {
        try {
            $resposta = Http::timeout(20)->get("https://www.googleapis.com/youtube/v3/{$recurso}", [
                ...$parametros,
                'key' => config('services.youtube.api_key'),
            ]);
        } catch (ConnectionException) {
            throw new RuntimeException('Não foi possível conectar ao YouTube. Tente novamente em alguns instantes.');
        }

        if ($resposta->status() === 404) {
            return null;
        }
        if ($resposta->failed()) {
            report(new RuntimeException('A API do YouTube retornou erro: '.$resposta->status()));
            throw new RuntimeException('Não foi possível consultar o YouTube. Verifique o canal e a chave da API.');
        }

        return $resposta->json();
    }

    private function videoId(string $url): ?string
    {
        preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/|live\/))([\w-]{11})/i', $url, $matches);

        return $matches[1] ?? null;
    }
}
