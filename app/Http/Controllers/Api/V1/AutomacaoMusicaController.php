<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Musica;
use App\Services\HtmlSanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Entrada do repertório por automação (token estático AUTOMACAO_TOKEN):
 * uma IA local lê a pasta de músicas do louvor e empurra uma por uma.
 */
class AutomacaoMusicaController extends Controller
{
    /** O que já está cadastrado, sem as letras, para a automação saber o que falta. */
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Musica::orderBy('nome')->orderBy('artista')->get()->map(fn (Musica $m) => $this->resumo($m)),
        ]);
    }

    /**
     * Cria ou atualiza. A música é identificada por nome + artista (sem diferenciar
     * maiúsculas e acentos), então reenviar corrige em vez de duplicar.
     * Campo que não vier no corpo fica como estava.
     */
    public function salvar(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:200'],
            'artista' => ['nullable', 'string', 'max:150'],
            'tom' => ['nullable', 'string', 'max:10'],
            'bpm' => ['nullable', 'integer', 'between:20,400'],
            'categoria' => ['nullable', Rule::in(array_keys(Musica::CATEGORIAS))],
            'link' => ['nullable', 'url', 'max:500'],
            'letra' => ['nullable', 'string'],
            'secoes_publico' => ['nullable', 'array'],
            'secoes_publico.*.tipo' => ['nullable', Rule::in(array_keys(Musica::TIPOS_SECAO))],
            // Seção sem texto é só descartada, não derruba o envio da música inteira
            'secoes_publico.*.texto' => ['nullable', 'string'],
        ]);

        $dados['nome'] = trim($dados['nome']);
        if (array_key_exists('artista', $dados)) {
            $dados['artista'] = trim((string) $dados['artista']) ?: null;
        }
        if (array_key_exists('tom', $dados)) {
            $dados['tom'] = Musica::normalizarTom($dados['tom']);
        }
        if (array_key_exists('letra', $dados)) {
            $dados['letra'] = HtmlSanitizer::clean($dados['letra'] ?? '');
        }
        if (array_key_exists('secoes_publico', $dados)) {
            $dados['secoes_publico'] = Musica::limparSecoes($dados['secoes_publico']);
        }

        $musica = $this->encontrar($dados['nome'], $dados['artista'] ?? null);
        $criada = $musica === null;

        if ($criada) {
            $musica = new Musica(['letra' => '']);
        } elseif (! $musica->artista) {
            // Já existia só com o nome: mantém o nome como está no painel
            unset($dados['nome']);
        }

        $musica->fill($dados)->save();

        return response()->json([
            'acao' => $criada ? 'criada' : 'atualizada',
            'musica' => $this->resumo($musica),
        ], $criada ? 201 : 200);
    }

    private function encontrar(string $nome, ?string $artista): ?Musica
    {
        $mesmoNome = Musica::all()->filter(fn (Musica $m) => Musica::chave($m->nome) === Musica::chave($nome));

        $exata = $mesmoNome->first(fn (Musica $m) => Musica::chave($m->artista) === Musica::chave($artista));
        if ($exata) {
            return $exata;
        }

        // Cadastrada à mão sem artista: se só há uma com esse nome, é ela (e ganha o artista).
        $semArtista = $mesmoNome->filter(fn (Musica $m) => ! $m->artista);

        return $artista && $semArtista->count() === 1 ? $semArtista->first() : null;
    }

    private function resumo(Musica $m): array
    {
        return [
            'id' => $m->id,
            'nome' => $m->nome,
            'artista' => $m->artista,
            'tom' => $m->tom,
            'bpm' => $m->bpm,
            'categoria' => $m->categoria,
            'link' => $m->link,
            'tem_letra' => trim(strip_tags((string) $m->letra)) !== '',
            'secoes_publico' => count($m->secoes_publico ?? []),
        ];
    }
}
