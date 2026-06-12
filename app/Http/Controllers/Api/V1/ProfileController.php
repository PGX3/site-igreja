<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateDisponibilidadeRequest;
use App\Http\Requests\Api\UpdatePasswordRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(Request $request): UserResource
    {
        $user = $request->user();
        $user->load('role');

        return new UserResource($user);
    }

    public function update(UpdateProfileRequest $request): UserResource
    {
        $user = $request->user();
        $user->fill($request->validated())->save();
        $user->load('role');

        return new UserResource($user);
    }

    public function updateDisponibilidade(UpdateDisponibilidadeRequest $request): UserResource
    {
        $user = $request->user();
        $user->disponibilidade = $request->validated('disponibilidade');
        $user->save();
        $user->load('role');

        return new UserResource($user);
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->password = Hash::make($request->validated('password'));
        $user->save();

        return response()->json(['message' => 'Senha atualizada.']);
    }
}
