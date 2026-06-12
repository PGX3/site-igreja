<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\MembroAdicionadoEscala;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddMembroEscalaRequest;
use App\Http\Requests\Api\IndexEscalasRequest;
use App\Http\Requests\Api\StoreEscalaRequest;
use App\Http\Requests\Api\UpdateEscalaRequest;
use App\Http\Resources\EscalaResource;
use App\Models\Escala;
use App\Models\EscalaMembro;
use App\Models\User;
use App\Services\EscalaConfirmacaoService;
use App\Services\MinhasEscalasQuery;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class EscalaController extends Controller
{
    public function index(IndexEscalasRequest $request, MinhasEscalasQuery $query): AnonymousResourceCollection
    {
        $escalas = $query->get($request->user(), $request->validated());

        return EscalaResource::collection($escalas);
    }

    public function store(StoreEscalaRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $escala = DB::transaction(function () use ($data, $user) {
            $escala = Escala::create([
                'titulo'      => $data['titulo'],
                'descricao'   => $data['descricao'] ?? null,
                'data'        => $data['data'],
                'hora_inicio' => $data['hora_inicio'],
                'hora_fim'    => $data['hora_fim'],
                'grupo_id'    => $data['grupo_id'],
                'culto_id'    => $data['culto_id'] ?? null,
                'evento_id'   => $data['evento_id'] ?? null,
                'created_by'  => $user->id,
                'status'      => 'pendente',
            ]);

            foreach ($data['membros'] ?? [] as $membro) {
                EscalaMembro::create([
                    'escala_id' => $escala->id,
                    'user_id'   => $membro['user_id'],
                    'funcao'    => $membro['funcao'] ?? null,
                ]);
            }

            return $escala;
        });

        $escala->load('grupo');
        foreach ($data['membros'] ?? [] as $membro) {
            $notificado = User::find($membro['user_id']);
            if ($notificado) {
                event(new MembroAdicionadoEscala($notificado, $escala, $membro['funcao'] ?? null));
            }
        }

        $escala->load(['grupo', 'culto', 'evento']);

        return (new EscalaResource($escala))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Escala $escala): EscalaResource
    {
        $user = $request->user();

        if ($user->canManageGrupo($escala->grupo_id)) {
            $escala->load(['grupo', 'culto', 'evento', 'escalaMembros.user']);
            return new EscalaResource($escala);
        }

        $escalaComPivot = $user
            ->escalas()
            ->with(['grupo', 'culto', 'evento'])
            ->where('escalas.id', $escala->id)
            ->first();

        if (! $escalaComPivot) {
            throw new AuthorizationException('Você não tem acesso a esta escala.');
        }

        return new EscalaResource($escalaComPivot);
    }

    public function update(UpdateEscalaRequest $request, Escala $escala): EscalaResource
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;

        $escala->update($data);
        $escala->load(['grupo', 'culto', 'evento', 'escalaMembros.user']);

        return new EscalaResource($escala);
    }

    public function destroy(Request $request, Escala $escala): JsonResponse
    {
        if (! $request->user()->canManageGrupo($escala->grupo_id)) {
            throw new AuthorizationException('Você não pode excluir esta escala.');
        }

        $escala->delete();

        return response()->json(['message' => 'Escala excluída.']);
    }

    public function addMembro(AddMembroEscalaRequest $request, Escala $escala): JsonResponse
    {
        $data = $request->validated();

        $membro = EscalaMembro::firstOrCreate(
            ['escala_id' => $escala->id, 'user_id' => $data['user_id']],
            ['funcao' => $data['funcao'] ?? null],
        );

        if ($membro->wasRecentlyCreated) {
            $notificado = User::find($data['user_id']);
            if ($notificado) {
                $escala->loadMissing('grupo');
                event(new MembroAdicionadoEscala($notificado, $escala, $data['funcao'] ?? null));
            }
        }

        $escala->load(['grupo', 'culto', 'evento', 'escalaMembros.user']);

        return (new EscalaResource($escala))
            ->response()
            ->setStatusCode($membro->wasRecentlyCreated ? 201 : 200);
    }

    public function removeMembro(Request $request, Escala $escala, EscalaMembro $escalaMembro): JsonResponse
    {
        if (! $request->user()->canManageGrupo($escala->grupo_id)) {
            throw new AuthorizationException('Você não pode remover membros desta escala.');
        }

        if ($escalaMembro->escala_id !== $escala->id) {
            throw new AuthorizationException('Membro não pertence a esta escala.');
        }

        $escalaMembro->delete();

        return response()->json(['message' => 'Membro removido.']);
    }

    public function confirmar(Request $request, EscalaMembro $escalaMembro, EscalaConfirmacaoService $service): EscalaResource
    {
        $service->confirmar($escalaMembro, $request->user());

        return $this->show($request, $escalaMembro->escala);
    }

    public function recusar(Request $request, EscalaMembro $escalaMembro, EscalaConfirmacaoService $service): EscalaResource
    {
        $service->recusar($escalaMembro, $request->user());

        return $this->show($request, $escalaMembro->escala);
    }
}
