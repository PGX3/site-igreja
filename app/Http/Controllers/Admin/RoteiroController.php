<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Roteiro;
use App\Services\HtmlSanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Roteiros de pregação: cada pregador escreve os seus; o pastor vê todos.
 * O editor salva sozinho (autosave via JSON em `update`).
 */
class RoteiroController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $roteiros = Roteiro::with('autor:id,name')
            ->when(! $user->isPastor(), fn ($q) => $q->where('user_id', $user->id))
            ->orderByDesc('data')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (Roteiro $r) => [
                'id' => $r->id,
                'titulo' => $r->titulo,
                'data' => $r->data->format('Y-m-d'),
                'versiculo' => $r->versiculo,
                'pregador' => $r->autor?->name,
                'meu' => $r->user_id === $user->id,
                'atualizado_em' => $r->updated_at?->toIso8601String(),
            ]);

        return Inertia::render('Admin/Roteiros/Index', [
            'roteiros' => $roteiros,
            'verTodos' => $user->isPastor(),
        ]);
    }

    /** "Novo roteiro" cria na hora um rascunho em branco e abre o editor (assim o autosave já tem onde salvar). */
    public function store(Request $request)
    {
        $roteiro = new Roteiro([
            'titulo' => 'Nova pregação',
            'data' => now()->next('Sunday')->toDateString(),
        ]);
        $roteiro->user_id = $request->user()->id;
        $roteiro->save();

        return redirect()->route('admin.roteiros.edit', $roteiro);
    }

    public function edit(Request $request, Roteiro $roteiro)
    {
        $this->autorizar($request, $roteiro);

        return Inertia::render('Admin/Roteiros/Editor', [
            'roteiro' => [
                'id' => $roteiro->id,
                'titulo' => $roteiro->titulo,
                'data' => $roteiro->data->format('Y-m-d'),
                'versiculo' => $roteiro->versiculo,
                'conteudo' => $roteiro->conteudo ?? '',
                'tamanho_fonte' => $roteiro->tamanho_fonte,
                'pregador' => $roteiro->autor?->name,
                'atualizado_em' => $roteiro->updated_at?->toIso8601String(),
            ],
            'fonteMin' => Roteiro::TAMANHO_FONTE_MIN,
            'fonteMax' => Roteiro::TAMANHO_FONTE_MAX,
        ]);
    }

    /** Modo púlpito no navegador: só o texto, em tela cheia (igual ao app do iPad, sem os botões do Spresenter). */
    public function pulpito(Request $request, Roteiro $roteiro)
    {
        $this->autorizar($request, $roteiro);

        return Inertia::render('Admin/Roteiros/Pulpito', [
            'roteiro' => [
                'id' => $roteiro->id,
                'titulo' => $roteiro->titulo,
                'data' => $roteiro->data->format('Y-m-d'),
                'versiculo' => $roteiro->versiculo,
                'conteudo' => $roteiro->conteudo ?? '',
                'tamanho_fonte' => $roteiro->tamanho_fonte,
            ],
            'fonteMin' => Roteiro::TAMANHO_FONTE_MIN,
            'fonteMax' => Roteiro::TAMANHO_FONTE_MAX,
        ]);
    }

    public function update(Request $request, Roteiro $roteiro): JsonResponse
    {
        $this->autorizar($request, $roteiro);

        $data = $request->validate([
            'titulo' => 'required|string|max:150',
            'data' => 'required|date',
            'versiculo' => 'nullable|string|max:120',
            'conteudo' => 'nullable|string|max:500000',
            'tamanho_fonte' => 'required|integer|min:'.Roteiro::TAMANHO_FONTE_MIN.'|max:'.Roteiro::TAMANHO_FONTE_MAX,
        ], [
            'titulo.required' => 'Dê um título para a pregação.',
        ]);

        $data['conteudo'] = HtmlSanitizer::clean($data['conteudo'] ?? null);

        $roteiro->update($data);

        return response()->json([
            'ok' => true,
            'atualizado_em' => $roteiro->updated_at->toIso8601String(),
        ]);
    }

    public function destroy(Request $request, Roteiro $roteiro)
    {
        $this->autorizar($request, $roteiro);

        $roteiro->delete();

        return redirect()->route('admin.roteiros.index')->with('success', 'Roteiro excluído.');
    }

    private function autorizar(Request $request, Roteiro $roteiro): void
    {
        abort_unless($roteiro->podeSerEditadoPor($request->user()), 403);
    }
}
