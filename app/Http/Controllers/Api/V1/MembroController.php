<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MembroController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $gruposGerenciados = $user->grupoIdsLiderados();

        if (! $user->isPastor() && empty($gruposGerenciados)) {
            throw new AuthorizationException('Você não tem permissão para listar membros.');
        }

        $request->validate([
            'grupo_id' => ['nullable', 'integer', 'exists:grupos,id'],
            'q' => ['nullable', 'string', 'max:100'],
            'tipo' => ['nullable', 'in:membro,visitante'],
        ]);

        $query = User::query()->orderBy('name');

        // Não-pastor só enxerga membros dos grupos que lidera.
        if (! $user->isPastor()) {
            $query->whereHas('grupos', fn ($g) => $g->whereIn('grupos.id', $gruposGerenciados));
        }

        if ($tipo = $request->input('tipo')) {
            $query->where('tipo', $tipo);
        }

        if ($grupoId = $request->input('grupo_id')) {
            if (! $user->isPastor() && ! in_array((int) $grupoId, $gruposGerenciados, true)) {
                throw new AuthorizationException('Você não pode listar membros deste grupo.');
            }

            $query->whereHas('grupos', fn ($g) => $g->where('grupos.id', $grupoId));
        }

        if ($termo = $request->input('q')) {
            $query->where(function ($q) use ($termo) {
                $q->where('name', 'like', "%{$termo}%")
                    ->orWhere('email', 'like', "%{$termo}%")
                    ->orWhere('telefone', 'like', "%{$termo}%");
            });
        }

        return UserResource::collection($query->limit(50)->get());
    }
}
