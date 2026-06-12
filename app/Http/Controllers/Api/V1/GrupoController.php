<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\GrupoResource;
use App\Models\Grupo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GrupoController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $query = Grupo::query()->orderBy('nome');

        if (! $user->isPastor()) {
            $query->whereIn('id', $user->grupoIds());
        }

        return GrupoResource::collection($query->get());
    }
}
