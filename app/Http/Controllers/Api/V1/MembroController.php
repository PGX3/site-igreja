<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MembroController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'grupo_id' => ['nullable', 'integer', 'exists:grupos,id'],
            'q'        => ['nullable', 'string', 'max:100'],
            'tipo'     => ['nullable', 'in:membro,visitante'],
        ]);

        $query = User::query()->orderBy('name');

        if ($tipo = $request->input('tipo')) {
            $query->where('tipo', $tipo);
        }

        if ($grupoId = $request->input('grupo_id')) {
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
