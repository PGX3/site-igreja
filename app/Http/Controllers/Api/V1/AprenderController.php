<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Aula;
use App\Models\Curso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Escola bíblica no app: mesma regra do painel (todos leem os cursos ativos,
 * pastor e líder também veem os ocultos para pré-visualizar).
 */
class AprenderController extends Controller
{
    public function index(): JsonResponse
    {
        $cursos = Curso::ativos()
            ->withCount('modulos')
            ->orderBy('ordem')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Curso $c) => [
                'id' => $c->id,
                'titulo' => $c->titulo,
                'descricao' => strip_tags((string) $c->descricao),
                'capa_url' => self::absoluta($c->capaUrl()),
                'cor' => $c->cor,
                'modulos_count' => $c->modulos_count,
            ]);

        return response()->json(['data' => $cursos]);
    }

    public function curso(Request $request, Curso $curso): JsonResponse
    {
        $gestor = self::ehGestor($request);
        abort_unless($curso->ativo || $gestor, 404);

        $dados = $curso->paraLeitura(fn (Aula $a) => route('api.v1.aprender.aula', $a->id), ! $gestor);
        $dados['capa_url'] = self::absoluta($dados['capa_url']);

        return response()->json(['data' => $dados + ['preview' => ! $curso->ativo]]);
    }

    public function aula(Request $request, Aula $aula): JsonResponse
    {
        $gestor = self::ehGestor($request);
        abort_unless($aula->ativo || $gestor, 404);
        $aula->load('modulo.curso', 'anexos.asset', 'comentarios.user:id,name');

        $dados = $aula->paraLeitura();
        $dados['anexos'] = $dados['anexos']->map(fn ($a) => ['url' => self::absoluta($a['url'])] + $a);

        $userId = $request->user()->id;

        return response()->json(['data' => $dados + [
            'curso_id' => $aula->modulo->curso_id,
            'curso_titulo' => $aula->modulo->curso->titulo,
            'modulo_titulo' => $aula->modulo->titulo,
            'preview' => ! $aula->ativo,
            'comentarios' => $aula->comentarios->map(fn ($c) => [
                'id' => $c->id,
                'corpo' => $c->corpo,
                'autor' => $c->user?->name ?? 'Usuário',
                'criado_em' => $c->created_at->toIso8601String(),
                'pode_excluir' => $c->user_id === $userId || $gestor,
            ])->values(),
        ]]);
    }

    private static function ehGestor(Request $request): bool
    {
        $u = $request->user();

        return $u->isPastor() || $u->isLider();
    }

    /** O app não resolve URL relativa (`/storage/...`), então devolve sempre absoluta. */
    private static function absoluta(?string $url): ?string
    {
        return $url ? url($url) : null;
    }
}
