<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EscalaResource;
use App\Models\Escala;
use App\Models\EscalaSetlistItem;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Setlist da escala pelo app (quem gerencia o grupo, e só em grupo com músicas).
 * Toda ação devolve a escala completa atualizada, igual ao GET /escalas/{id}.
 */
class EscalaSetlistController extends Controller
{
    public function store(Request $request, Escala $escala): EscalaResource
    {
        $this->autorizar($request, $escala);

        $data = $request->validate([
            'musica_id' => 'required|exists:musicas,id',
            'tom' => 'nullable|string|max:10',
            'observacao' => 'nullable|string|max:255',
        ], [], ['musica_id' => 'música']);

        if ($escala->setlist()->where('musica_id', $data['musica_id'])->exists()) {
            throw ValidationException::withMessages(['musica_id' => 'Essa música já está no setlist.']);
        }

        $escala->setlist()->create([
            'musica_id' => $data['musica_id'],
            'tom' => $data['tom'] ?? null,
            'observacao' => $data['observacao'] ?? null,
            'ordem' => (int) $escala->setlist()->max('ordem') + 1,
        ]);

        return self::escalaCompleta($escala);
    }

    public function update(Request $request, Escala $escala, EscalaSetlistItem $item): EscalaResource
    {
        $this->autorizar($request, $escala);
        abort_unless($item->escala_id === $escala->id, 404);

        $data = $request->validate([
            'tom' => 'nullable|string|max:10',
            'observacao' => 'nullable|string|max:255',
        ]);

        $item->update([
            'tom' => $data['tom'] ?? null,
            'observacao' => $data['observacao'] ?? null,
        ]);

        return self::escalaCompleta($escala);
    }

    /** `itens`: ids dos itens do setlist na nova ordem. */
    public function reorder(Request $request, Escala $escala): EscalaResource
    {
        $this->autorizar($request, $escala);

        $data = $request->validate([
            'itens' => 'required|array',
            'itens.*' => 'integer',
        ]);

        foreach ($data['itens'] as $ordem => $id) {
            $escala->setlist()->where('id', $id)->update(['ordem' => $ordem]);
        }

        return self::escalaCompleta($escala);
    }

    public function destroy(Request $request, Escala $escala, EscalaSetlistItem $item): EscalaResource
    {
        $this->autorizar($request, $escala);
        abort_unless($item->escala_id === $escala->id, 404);

        $item->delete();

        return self::escalaCompleta($escala);
    }

    private function autorizar(Request $request, Escala $escala): void
    {
        abort_unless($request->user()->canManageGrupo($escala->grupo_id), 403);
        abort_unless($escala->grupo?->tem_musicas, 403);
    }

    public static function escalaCompleta(Escala $escala): EscalaResource
    {
        return new EscalaResource($escala->fresh([
            'grupo', 'culto', 'evento', 'escalaMembros.user',
            'setlist.musica', 'notas.createdBy', 'assets',
        ]));
    }
}
