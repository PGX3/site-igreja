<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Pregacao;
use Illuminate\Http\JsonResponse;

/** Pregações publicadas (as mesmas da página pública /pregacoes). */
class PregacaoController extends Controller
{
    public function index(): JsonResponse
    {
        $pregacoes = Pregacao::where('ativo', true)
            ->orderByDesc('data_pregacao')
            ->get()
            ->map(fn (Pregacao $p) => self::pregacao($p));

        return response()->json(['data' => $pregacoes]);
    }

    public function show(Pregacao $pregacao): JsonResponse
    {
        abort_unless($pregacao->ativo, 404);

        return response()->json(['data' => self::pregacao($pregacao) + ['descricao' => $pregacao->descricao]]);
    }

    private static function pregacao(Pregacao $p): array
    {
        return [
            'id' => $p->id,
            'titulo' => $p->titulo,
            'pregador' => $p->pregador,
            'versiculo' => $p->versiculo,
            'youtube_url' => $p->youtube_url,
            'data_pregacao' => $p->data_pregacao?->format('Y-m-d'),
        ];
    }
}
