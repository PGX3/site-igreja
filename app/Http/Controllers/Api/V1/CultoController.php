<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CultoResource;
use App\Models\Culto;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CultoController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $cultos = Culto::query()
            ->where('ativo', true)
            ->orderBy('id')
            ->get();

        return CultoResource::collection($cultos);
    }

    public function show(Culto $culto): CultoResource
    {
        return new CultoResource($culto);
    }
}
