<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCadastroRequest;
use App\Http\Resources\UserResource;
use App\Models\Familia;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CadastroController extends Controller
{
    public function store(StoreCadastroRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data) {
            $familiaId = null;

            if (! empty($data['endereco']) || ! empty($data['cidade']) || ! empty($data['cep'])) {
                $familia = Familia::create([
                    'endereco' => $data['endereco'] ?? '',
                    'cidade' => $data['cidade'] ?? '',
                    'uf' => $data['uf'] ?? '',
                    'cep' => $data['cep'] ?? '',
                    'telefone_principal' => $data['telefone'] ?? null,
                ]);
                $familiaId = $familia->id;
            }

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'telefone' => $data['telefone'],
                'data_nascimento' => $data['data_nascimento'] ?? null,
                'sexo' => $data['sexo'] ?? null,
                'estado_civil' => $data['estado_civil'] ?? null,
                'cpf' => $data['cpf'] ?? null,
                'como_conheceu' => $data['como_conheceu'] ?? null,
                'primeira_visita' => $data['primeira_visita'] ?? null,
                'tipo' => $data['tipo'],
                'batizado_aguas' => $data['batizado_aguas'],
                'familia_id' => $familiaId,
            ]);

            if ($familiaId) {
                Familia::where('id', $familiaId)->update(['responsavel_id' => $user->id]);
            }

            return $user;
        });

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }
}
