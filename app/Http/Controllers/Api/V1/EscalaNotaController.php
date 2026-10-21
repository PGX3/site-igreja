<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EscalaResource;
use App\Models\Escala;
use App\Models\EscalaNota;
use Illuminate\Http\Request;

/** Notas da escala pelo app (quem gerencia o grupo). Devolve a escala atualizada. */
class EscalaNotaController extends Controller
{
    public function store(Request $request, Escala $escala): EscalaResource
    {
        abort_unless($request->user()->canManageGrupo($escala->grupo_id), 403);

        $data = $request->validate([
            'titulo' => 'nullable|string|max:200',
            'corpo' => 'required|string',
        ], [], ['corpo' => 'texto']);

        $escala->notas()->create([
            'titulo' => $data['titulo'] ?? null,
            'corpo' => $data['corpo'],
            'created_by' => $request->user()->id,
        ]);

        return EscalaSetlistController::escalaCompleta($escala);
    }

    public function destroy(Request $request, Escala $escala, EscalaNota $nota): EscalaResource
    {
        abort_unless($request->user()->canManageGrupo($escala->grupo_id), 403);
        abort_unless($nota->escala_id === $escala->id, 404);

        $nota->delete();

        return EscalaSetlistController::escalaCompleta($escala);
    }
}
