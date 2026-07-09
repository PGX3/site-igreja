<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aula;
use App\Models\Curso;
use App\Models\CursoModulo;
use Inertia\Inertia;

class AprenderController extends Controller
{
    public function index()
    {
        $cursos = Curso::ativos()
            ->withCount(['modulos'])
            ->orderBy('ordem')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'titulo' => $c->titulo,
                'descricao' => strip_tags((string) $c->descricao),
                'capa_url' => $c->capaUrl(),
            ]);

        return Inertia::render('Admin/Cursos/Aprender', compact('cursos'));
    }

    public function curso(Curso $curso)
    {
        $gestor = $this->ehGestor();
        abort_unless($curso->ativo || $gestor, 404);

        return Inertia::render('Admin/Cursos/AprenderCurso', [
            'curso' => $curso->paraLeitura(fn (Aula $a) => route('admin.aprender.aula', $a->id), ! $gestor),
            'preview' => $gestor && ! $curso->ativo,
        ]);
    }

    public function aula(Aula $aula)
    {
        $gestor = $this->ehGestor();
        abort_unless($aula->ativo || $gestor, 404);
        $aula->load('modulo.curso', 'anexos.asset', 'comentarios.user:id,name');

        $userId = auth()->id();

        return Inertia::render('Admin/Cursos/AprenderAula', [
            'aula' => $aula->paraLeitura(),
            'cursoTitulo' => $aula->modulo->curso->titulo,
            'voltarUrl' => route('admin.aprender.curso', $aula->modulo->curso_id),
            'preview' => $gestor && ! $aula->ativo,
            'comentarios' => $aula->comentarios->map(fn ($c) => [
                'id' => $c->id,
                'corpo' => $c->corpo,
                'autor' => $c->user?->name ?? 'Usuário',
                'data' => $c->created_at->format('d/m/Y H:i'),
                'pode_excluir' => $c->user_id === $userId || $gestor,
            ]),
        ]);
    }

    // ─────────────── PDF (imprimir / salvar como livro) ───────────────

    public function pdfAula(Aula $aula)
    {
        $gestor = $this->ehGestor();
        abort_unless($aula->ativo || $gestor, 404);
        $aula->load('modulo.curso', 'anexos.asset');

        return view('print.aula', ['aula' => $aula]);
    }

    public function pdfModulo(CursoModulo $modulo)
    {
        $gestor = $this->ehGestor();
        $modulo->load(['curso', 'aulas' => fn ($q) => $gestor ? $q : $q->where('ativo', true), 'aulas.anexos.asset']);
        abort_unless($modulo->curso->ativo || $gestor, 404);

        return view('print.modulo', ['modulo' => $modulo]);
    }

    public function pdfCurso(Curso $curso)
    {
        $gestor = $this->ehGestor();
        abort_unless($curso->ativo || $gestor, 404);
        $curso->load(['modulos.aulas' => fn ($q) => $gestor ? $q : $q->where('ativo', true), 'modulos.aulas.anexos.asset']);

        return view('print.curso', ['curso' => $curso]);
    }

    private function ehGestor(): bool
    {
        $u = auth()->user();

        return $u && ($u->isPastor() || $u->isLider());
    }
}
