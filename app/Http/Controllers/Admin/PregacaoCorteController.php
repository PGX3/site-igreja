<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pregacao;
use App\Models\PregacaoCorte;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

/**
 * Cortes (Shorts) dentro da pregação. Só o pastor: adiciona por link, troca de
 * pregação (fica fixado, o importador não mexe mais) e esconde do site.
 * Responde JSON para não recarregar o editor do roteiro.
 */
class PregacaoCorteController extends Controller
{
    public function store(Request $request, Pregacao $pregacao): JsonResponse
    {
        $request->validate(['url' => 'required|string|max:255'], ['url.required' => 'Cole o link do Short.']);

        preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/|live\/))([\w-]{11})/i', $request->input('url'), $m);
        $videoId = $m[1] ?? null;
        if (! $videoId) {
            return response()->json(['errors' => ['url' => ['Link do YouTube inválido.']]], 422);
        }
        if (Pregacao::where('youtube_url', 'like', "%{$videoId}%")->exists()) {
            return response()->json(['errors' => ['url' => ['Esse vídeo é uma pregação, não um corte.']]], 422);
        }

        $corte = PregacaoCorte::firstOrNew(['youtube_id' => $videoId]);
        if (! $corte->exists) {
            $corte->titulo = PregacaoCorte::limparTitulo($this->tituloNoYoutube($videoId) ?? 'Corte');
            $corte->publicado_em = now();
        }
        $corte->pregacao_id = $pregacao->id;
        $corte->fixado = true;
        $corte->ativo = true;
        $corte->save();

        return response()->json(['ok' => true, 'cortes' => self::listaAdmin($pregacao)]);
    }

    public function update(Request $request, PregacaoCorte $corte): JsonResponse
    {
        $data = $request->validate([
            'pregacao_id' => ['sometimes', 'nullable', 'integer', Rule::exists('pregacoes', 'id')],
            'ativo' => 'sometimes|boolean',
        ]);

        if (array_key_exists('pregacao_id', $data) && $data['pregacao_id'] !== $corte->pregacao_id) {
            $corte->fixado = true;
        }
        $corte->fill($data)->save();

        return response()->json(['ok' => true]);
    }

    /** Lista do painel (inclui os escondidos). */
    public static function listaAdmin(Pregacao $pregacao): array
    {
        return $pregacao->cortes()->orderBy('publicado_em')->orderBy('id')->get()
            ->map(fn (PregacaoCorte $c) => $c->paraExibir() + ['ativo' => $c->ativo, 'fixado' => $c->fixado])
            ->all();
    }

    private function tituloNoYoutube(string $videoId): ?string
    {
        try {
            $resposta = Http::timeout(8)->get('https://www.youtube.com/oembed', [
                'url' => "https://www.youtube.com/shorts/{$videoId}",
                'format' => 'json',
            ]);
        } catch (ConnectionException) {
            return null;
        }

        return $resposta->successful() ? ($resposta->json('title') ?: null) : null;
    }
}
