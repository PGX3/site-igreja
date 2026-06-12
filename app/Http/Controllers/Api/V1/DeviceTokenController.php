<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreDeviceTokenRequest;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function store(StoreDeviceTokenRequest $request): JsonResponse
    {
        $data = $request->validated();

        DeviceToken::updateOrCreate(
            ['token' => $data['token']],
            [
                'user_id'      => $request->user()->id,
                'platform'     => $data['platform'],
                'app_version'  => $data['app_version'] ?? null,
                'last_used_at' => now(),
            ],
        );

        return response()->json(['message' => 'Device registrado.'], 201);
    }

    public function destroy(Request $request, string $token): JsonResponse
    {
        DeviceToken::where('token', $token)
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['message' => 'Device removido.']);
    }
}
