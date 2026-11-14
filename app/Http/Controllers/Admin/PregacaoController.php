<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Igreja;
use App\Models\Pregacao;
use App\Models\User;
use App\Services\HtmlSanitizer;
use App\Services\YouTubePregacoesImporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use RuntimeException;

/**
 * Pregações: do rascunho (roteiro do pregador, lido no iPad do púlpito) à
 * publicação no site (link do YouTube). O pastor vê e publica todas; o
 * pregador vê e edita as suas. O editor salva sozinho (update devolve JSON).
 */
class PregacaoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $pregacoes = Pregacao::query()
            ->when(! $user->isPastor(), fn ($q) => $q->where('pregador_id', $user->id))
            ->orderByDesc('data_pregacao')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Pregacao $p) => [
                'id' => $p->id,
                'titulo' => $p->titulo,
                'pregador' => $p->pregador,
                'versiculo' => $p->versiculo,
                'youtube_url' => $p->youtube_url,
                'data_pregacao' => $p->data_pregacao->format('Y-m-d'),
                'status' => $this->status($p),
                'tem_roteiro' => filled(strip_tags((string) $p->roteiro)),
            ]);

        return Inertia::render('Admin/Pregacoes/Index', [
            'pregacoes' => $pregacoes,
            'ehPastor' => $user->isPastor(),
            'youtubeConfigurado' => $user->isPastor() && (bool) Igreja::atual()->youtube_channel_id,
        ]);
    }

    public function importarYoutube(YouTubePregacoesImporter $importador)
    {
        $canalId = Igreja::atual()->youtube_channel_id;
        if (! $canalId) {
            return back()->with('error', 'Cadastre o canal do YouTube nos Dados da Igreja antes de importar.');
        }

        try {
            $quantidade = $importador->importar($canalId);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $mensagem = $quantidade
            ? "{$quantidade} ".($quantidade === 1 ? 'vídeo importado!' : 'vídeos importados!')
            : 'Nenhum vídeo novo para importar.';

        return back()->with('success', $mensagem);
    }

    /** "Nova pregação" cria na hora um rascunho e abre o editor (o autosave já tem onde salvar). */
    public function store(Request $request)
    {
        $user = $request->user();

        $pregacao = new Pregacao([
            'titulo' => 'Nova pregação',
            'data_pregacao' => now()->next('Sunday')->toDateString(),
            'pregador' => $user->name,
            'ativo' => true,
        ]);
        $pregacao->pregador_id = $user->id;
        $pregacao->save();

        return redirect()->route('admin.pregacoes.edit', $pregacao);
    }

    public function edit(Request $request, Pregacao $pregacao)
    {
        $this->autorizar($request, $pregacao);
        $ehPastor = $request->user()->isPastor();

        return Inertia::render('Admin/Pregacoes/Form', [
            'pregacao' => [
                'id' => $pregacao->id,
                'titulo' => $pregacao->titulo,
                'data_pregacao' => $pregacao->data_pregacao->format('Y-m-d'),
                'pregador' => $pregacao->pregador,
                'pregador_id' => $pregacao->pregador_id,
                'versiculo' => $pregacao->versiculo,
                'youtube_url' => $pregacao->youtube_url,
                'descricao' => $pregacao->descricao,
                'ativo' => $pregacao->ativo,
                'roteiro' => $pregacao->roteiro ?? '',
                'roteiro_fonte' => $pregacao->roteiro_fonte,
                'status' => $this->status($pregacao),
                'atualizado_em' => $pregacao->updated_at?->toIso8601String(),
            ],
            'ehPastor' => $ehPastor,
            'pregadores' => $ehPastor ? $this->pregadores() : [],
            'fonteMin' => Pregacao::ROTEIRO_FONTE_MIN,
            'fonteMax' => Pregacao::ROTEIRO_FONTE_MAX,
        ]);
    }

    public function update(Request $request, Pregacao $pregacao): JsonResponse
    {
        $this->autorizar($request, $pregacao);
        $ehPastor = $request->user()->isPastor();

        $regras = [
            'titulo' => 'required|string|max:150',
            'data_pregacao' => 'required|date',
            'versiculo' => 'nullable|string|max:120',
            'roteiro' => 'nullable|string|max:500000',
            'roteiro_fonte' => 'required|integer|min:'.Pregacao::ROTEIRO_FONTE_MIN.'|max:'.Pregacao::ROTEIRO_FONTE_MAX,
        ];
        if ($ehPastor) {
            // Publicar no site e escolher o pregador: só o pastor.
            $regras += [
                'youtube_url' => 'nullable|url|max:255',
                'descricao' => 'nullable|string',
                'ativo' => 'boolean',
                'pregador_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
                'pregador' => 'nullable|string|max:120',
            ];
        }

        $data = $request->validate($regras, [
            'titulo.required' => 'Dê um título para a pregação.',
            'youtube_url.url' => 'Cole o link completo do YouTube (https://...).',
        ]);
        $data['roteiro'] = HtmlSanitizer::clean($data['roteiro'] ?? null);

        if ($ehPastor && array_key_exists('pregador_id', $data)) {
            $pregacao->pregador_id = $data['pregador_id'];
            if ($data['pregador_id']) {
                $data['pregador'] = User::whereKey($data['pregador_id'])->value('name');
            }
            unset($data['pregador_id']);
        }

        $pregacao->update($data);

        return response()->json([
            'ok' => true,
            'status' => $this->status($pregacao),
            'atualizado_em' => $pregacao->updated_at->toIso8601String(),
        ]);
    }

    /** Modo púlpito no navegador: só o roteiro, em tela cheia (igual ao app do iPad, sem os botões do Spresenter). */
    public function pulpito(Request $request, Pregacao $pregacao)
    {
        $this->autorizar($request, $pregacao);

        return Inertia::render('Admin/Pregacoes/Pulpito', [
            'roteiro' => [
                'id' => $pregacao->id,
                'titulo' => $pregacao->titulo,
                'data' => $pregacao->data_pregacao->format('Y-m-d'),
                'versiculo' => $pregacao->versiculo,
                'conteudo' => $pregacao->roteiro ?? '',
                'tamanho_fonte' => $pregacao->roteiro_fonte,
            ],
            'fonteMin' => Pregacao::ROTEIRO_FONTE_MIN,
            'fonteMax' => Pregacao::ROTEIRO_FONTE_MAX,
        ]);
    }

    public function destroy(Request $request, Pregacao $pregacao)
    {
        $this->autorizar($request, $pregacao);
        // Pregador só apaga rascunho; pregação com vídeo, só o pastor.
        abort_if(! $request->user()->isPastor() && filled($pregacao->youtube_url), 403);

        $pregacao->delete();

        return redirect()->route('admin.pregacoes.index')->with('success', 'Pregação removida!');
    }

    private function autorizar(Request $request, Pregacao $pregacao): void
    {
        abort_unless($pregacao->podeSerEditadaPor($request->user()), 403);
    }

    /** rascunho (sem vídeo) | publicada | oculta (tem vídeo mas não aparece no site) */
    private function status(Pregacao $p): string
    {
        if (blank($p->youtube_url)) {
            return 'rascunho';
        }

        return $p->ativo ? 'publicada' : 'oculta';
    }

    private function pregadores(): array
    {
        return User::query()
            ->where('is_superadmin', false)
            ->where(fn ($q) => $q->where('is_pregador', true)->orWhereHas('role', fn ($r) => $r->where('name', 'pastor')))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $u) => ['id' => $u->id, 'name' => $u->name])
            ->all();
    }
}
