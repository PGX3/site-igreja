<?php

namespace App\Services;

use App\Models\Pregacao;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class YouTubePregacoesImporter
{
    public function importar(string $channelId): int
    {
        $apiKey = config('services.youtube.api_key');

        if (! $apiKey) {
            return $this->importarFeedPublico($channelId);
        }

        $uploadsPlaylistId = data_get($this->buscar('channels', [
            'part' => 'contentDetails',
            'id' => $channelId,
        ]), 'items.0.contentDetails.relatedPlaylists.uploads');

        if (! $uploadsPlaylistId) {
            throw new RuntimeException('Não foi possível localizar o canal do YouTube configurado.');
        }

        $videoIdsCadastrados = Pregacao::query()
            ->pluck('youtube_url')
            ->map(fn ($url) => $this->videoId((string) $url))
            ->filter()
            ->flip()
            ->all();

        $importadas = 0;
        $pageToken = null;

        do {
            $pagina = $this->buscar('playlistItems', array_filter([
                'part' => 'snippet',
                'playlistId' => $uploadsPlaylistId,
                'maxResults' => 50,
                'pageToken' => $pageToken,
            ]));

            foreach ($pagina['items'] ?? [] as $item) {
                $videoId = data_get($item, 'snippet.resourceId.videoId');
                if (! $videoId || isset($videoIdsCadastrados[$videoId])) {
                    continue;
                }

                $titulo = trim((string) data_get($item, 'snippet.title'));
                $publicadoEm = data_get($item, 'snippet.publishedAt');
                if (! $titulo || ! $publicadoEm) {
                    continue;
                }

                Pregacao::create([
                    'titulo' => Str::limit($titulo, 150, ''),
                    'youtube_url' => "https://www.youtube.com/watch?v={$videoId}",
                    'descricao' => data_get($item, 'snippet.description') ?: null,
                    'data_pregacao' => Carbon::parse($publicadoEm)->toDateString(),
                    'ativo' => true,
                ]);

                $videoIdsCadastrados[$videoId] = true;
                $importadas++;
            }

            $pageToken = $pagina['nextPageToken'] ?? null;
        } while ($pageToken);

        return $importadas;
    }

    /**
     * O feed público não exige chave, mas limita a consulta aos vídeos mais recentes.
     */
    private function importarFeedPublico(string $channelId): int
    {
        try {
            $resposta = Http::timeout(20)->get('https://www.youtube.com/feeds/videos.xml', [
                'channel_id' => $channelId,
            ]);
        } catch (ConnectionException) {
            throw new RuntimeException('Não foi possível conectar ao YouTube. Tente novamente em alguns instantes.');
        }

        if ($resposta->failed()) {
            throw new RuntimeException('Não foi possível acessar o canal do YouTube configurado.');
        }

        libxml_use_internal_errors(true);
        $feed = simplexml_load_string($resposta->body());
        libxml_clear_errors();

        if (! $feed) {
            throw new RuntimeException('O YouTube não retornou um feed válido para este canal.');
        }

        $namespaces = $feed->getNamespaces(true);
        $yt = $namespaces['yt'] ?? null;
        $media = $namespaces['media'] ?? null;
        if (! $yt) {
            throw new RuntimeException('O feed do YouTube não contém vídeos para importar.');
        }

        $cadastrados = Pregacao::query()
            ->pluck('youtube_url')
            ->map(fn ($url) => $this->videoId((string) $url))
            ->filter()
            ->flip()
            ->all();
        $importadas = 0;

        foreach ($feed->entry ?? [] as $entry) {
            $ytEntry = $entry->children($yt);
            $videoId = (string) ($ytEntry->videoId ?? '');
            if (! $videoId || isset($cadastrados[$videoId])) {
                continue;
            }

            $mediaEntry = $media ? $entry->children($media) : null;
            $description = $mediaEntry?->group?->description;
            $this->criarPregacao(
                $videoId,
                (string) $entry->title,
                (string) $entry->published,
                $description ? (string) $description : null,
            );

            $cadastrados[$videoId] = true;
            $importadas++;
        }

        return $importadas;
    }

    private function criarPregacao(string $videoId, string $titulo, string $publicadoEm, ?string $descricao): void
    {
        Pregacao::create([
            'titulo' => Str::limit(trim($titulo), 150, ''),
            'youtube_url' => "https://www.youtube.com/watch?v={$videoId}",
            'descricao' => $descricao ?: null,
            'data_pregacao' => Carbon::parse($publicadoEm)->toDateString(),
            'ativo' => true,
        ]);
    }

    private function buscar(string $recurso, array $parametros): array
    {
        try {
            $resposta = Http::timeout(20)->get("https://www.googleapis.com/youtube/v3/{$recurso}", [
                ...$parametros,
                'key' => config('services.youtube.api_key'),
            ]);
        } catch (ConnectionException) {
            throw new RuntimeException('Não foi possível conectar ao YouTube. Tente novamente em alguns instantes.');
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
