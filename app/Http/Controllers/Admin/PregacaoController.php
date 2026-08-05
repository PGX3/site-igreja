<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Igreja;
use App\Models\Pregacao;
use App\Services\YouTubePregacoesImporter;
use Illuminate\Http\Request;
use RuntimeException;
use Inertia\Inertia;

class PregacaoController extends Controller
{
    public function index()
    {
        $pregacoes = Pregacao::orderByDesc('data_pregacao')->get()->map(fn ($p) => [
            'id' => $p->id,
            'titulo' => $p->titulo,
            'pregador' => $p->pregador,
            'versiculo' => $p->versiculo,
            'youtube_url' => $p->youtube_url,
            'descricao' => $p->descricao,
            'data_pregacao' => $p->data_pregacao->format('Y-m-d'),
            'ativo' => $p->ativo,
        ]);

        return Inertia::render('Admin/Pregacoes/Index', [
            'pregacoes' => $pregacoes,
            'youtubeConfigurado' => (bool) Igreja::atual()->youtube_channel_id,
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
            ? "{$quantidade} ".($quantidade === 1 ? 'pregação importada!' : 'pregações importadas!')
            : 'Nenhum vídeo novo para importar.';

        return back()->with('success', $mensagem);
    }

    public function create()
    {
        return Inertia::render('Admin/Pregacoes/Form', ['pregacao' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        Pregacao::create($data);

        return redirect()->route('admin.pregacoes.index')->with('success', 'Pregação criada!');
    }

    public function edit(Pregacao $pregacao)
    {
        return Inertia::render('Admin/Pregacoes/Form', [
            'pregacao' => array_merge($pregacao->toArray(), [
                'data_pregacao' => $pregacao->data_pregacao->format('Y-m-d'),
            ]),
        ]);
    }

    public function update(Request $request, Pregacao $pregacao)
    {
        $data = $this->validateData($request);

        $pregacao->update($data);

        return redirect()->route('admin.pregacoes.index')->with('success', 'Pregação atualizada!');
    }

    public function destroy(Pregacao $pregacao)
    {
        $pregacao->delete();

        return redirect()->route('admin.pregacoes.index')->with('success', 'Pregação removida!');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'titulo' => 'required|string|max:150',
            'pregador' => 'nullable|string|max:120',
            'versiculo' => 'nullable|string|max:120',
            'youtube_url' => 'required|url|max:255',
            'descricao' => 'nullable|string',
            'data_pregacao' => 'required|date',
            'ativo' => 'boolean',
        ]);
    }
}
