<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventoResource;
use App\Models\Evento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EventoController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Evento::query()->where('ativo', true);

        if (! $request->boolean('incluir_passados')) {
            $query->whereDate('data_evento', '>=', Carbon::today());
        }

        $eventos = $query->orderBy('data_evento')->get();

        return EventoResource::collection($eventos);
    }

    public function show(Evento $evento): EventoResource
    {
        return new EventoResource($evento);
    }
}
