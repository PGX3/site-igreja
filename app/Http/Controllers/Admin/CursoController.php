<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Services\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CursoController extends Controller
{
    public function index()
    {
        $cursos = Curso::withCount('modulos')
            ->orderBy('ordem')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'titulo' => $c->titulo,
                'ativo' => $c->ativo,
                'capa_url' => $c->capa_path ? Storage::url($c->capa_path) : null,
                'modulos_count' => $c->modulos_count,
                'compartilhado' => (bool) $c->share_token,
            ]);

        return Inertia::render('Admin/Cursos/Index', compact('cursos'));
    }

    public function create()
    {
        return Inertia::render('Admin/Cursos/Form', ['curso' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $curso = new Curso([
            'titulo' => $data['titulo'],
            'descricao' => $data['descricao'] ?? null,
            'cor' => $data['cor'] ?? '#2563eb',
            'ativo' => $data['ativo'] ?? true,
            'created_by' => auth()->id(),
        ]);

        if ($request->hasFile('capa')) {
            $curso->capa_path = $request->file('capa')->store('cursos', 'public');
        }

        $curso->save();

        return redirect()->route('admin.cursos.conteudo', $curso)->with('success', 'Curso criado! Agora adicione os módulos e aulas.');
    }

    public function edit(Curso $curso)
    {
        return Inertia::render('Admin/Cursos/Form', [
            'curso' => [
                'id' => $curso->id,
                'titulo' => $curso->titulo,
                'descricao' => $curso->descricao,
                'cor' => $curso->cor,
                'ativo' => $curso->ativo,
                'capa_url' => $curso->capa_path ? Storage::url($curso->capa_path) : null,
            ],
        ]);
    }

    public function update(Request $request, Curso $curso)
    {
        $data = $this->validateData($request);

        $curso->titulo = $data['titulo'];
        $curso->descricao = $data['descricao'] ?? null;
        $curso->cor = $data['cor'] ?? $curso->cor;
        $curso->ativo = $data['ativo'] ?? false;

        if ($request->hasFile('capa')) {
            if ($curso->capa_path) {
                Storage::disk('public')->delete($curso->capa_path);
            }
            $curso->capa_path = $request->file('capa')->store('cursos', 'public');
        }

        $curso->save();

        return redirect()->route('admin.cursos.index')->with('success', 'Curso atualizado!');
    }

    public function destroy(Curso $curso)
    {
        if ($curso->capa_path) {
            Storage::disk('public')->delete($curso->capa_path);
        }

        $curso->delete();

        return redirect()->route('admin.cursos.index')->with('success', 'Curso removido.');
    }

    /**
     * Montador do curso: gerencia módulos e aulas.
     */
    public function conteudo(Curso $curso)
    {
        $curso->load('modulos.aulas');

        return Inertia::render('Admin/Cursos/Builder', [
            'curso' => [
                'id' => $curso->id,
                'titulo' => $curso->titulo,
                'ativo' => $curso->ativo,
                'share_url' => $curso->share_token ? url("/c/{$curso->share_token}") : null,
                'modulos' => $curso->modulos->map(fn ($m) => [
                    'id' => $m->id,
                    'titulo' => $m->titulo,
                    'descricao' => $m->descricao,
                    'ordem' => $m->ordem,
                    'aulas' => $m->aulas->map(fn ($a) => [
                        'id' => $a->id,
                        'titulo' => $a->titulo,
                        'tipo' => $a->tipo,
                        'ordem' => $a->ordem,
                        'ativo' => $a->ativo,
                        'share_url' => $a->share_token ? url("/a/{$a->share_token}") : null,
                    ]),
                ]),
            ],
        ]);
    }

    public function compartilhar(Curso $curso)
    {
        $curso->gerarShareToken();

        return back()->with('success', 'Link do curso gerado.');
    }

    public function revogar(Curso $curso)
    {
        $curso->revogarLink();

        return back()->with('success', 'Link do curso revogado.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:150',
            'descricao' => 'nullable|string',
            'cor' => 'nullable|string|max:7',
            'ativo' => 'boolean',
            'capa' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:20480',
        ], [], ['capa' => 'capa']);

        $data['descricao'] = HtmlSanitizer::clean($data['descricao'] ?? null);

        return $data;
    }
}
