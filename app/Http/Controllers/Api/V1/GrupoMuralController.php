<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Grupo;
use App\Models\GrupoAviso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Mural de avisos do grupo, mesma regra do painel: lê quem é membro do grupo
 * (ou pastor), publica e remove quem gerencia o grupo (pastor ou líder dele).
 */
class GrupoMuralController extends Controller
{
    public function index(Request $request, Grupo $grupo): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->isPastor() || $grupo->membros()->where('users.id', $user->id)->exists(), 403);

        $avisos = $grupo->avisos()
            ->with('autor:id,name')
            ->orderByDesc('fixado')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (GrupoAviso $a) => self::aviso($a));

        return response()->json([
            'data' => [
                'grupo' => ['id' => $grupo->id, 'nome' => $grupo->nome],
                'pode_gerenciar' => $user->canManageGrupo($grupo->id),
                'avisos' => $avisos,
            ],
        ]);
    }

    public function store(Request $request, Grupo $grupo): JsonResponse
    {
        abort_unless($request->user()->canManageGrupo($grupo->id), 403);

        $data = $request->validate([
            'titulo' => 'required|string|max:200',
            'corpo' => 'required|string',
            'fixado' => 'boolean',
        ]);

        $aviso = $grupo->avisos()->create([
            'autor_id' => $request->user()->id,
            'titulo' => $data['titulo'],
            'corpo' => $data['corpo'],
            'fixado' => $data['fixado'] ?? false,
        ]);

        return response()->json(['data' => self::aviso($aviso->load('autor:id,name'))], 201);
    }

    public function destroy(Request $request, Grupo $grupo, GrupoAviso $aviso): JsonResponse
    {
        abort_unless($request->user()->canManageGrupo($grupo->id), 403);
        abort_unless($aviso->grupo_id === $grupo->id, 404);

        $aviso->delete();

        return response()->json(['message' => 'Aviso removido.']);
    }

    private static function aviso(GrupoAviso $a): array
    {
        return [
            'id' => $a->id,
            'titulo' => $a->titulo,
            'corpo' => $a->corpo,
            'fixado' => (bool) $a->fixado,
            'autor' => $a->autor ? ['id' => $a->autor->id, 'nome' => $a->autor->name] : null,
            'criado_em' => $a->created_at->toIso8601String(),
        ];
    }
}
