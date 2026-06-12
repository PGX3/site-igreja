<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class MinhasEscalasQuery
{
    public function get(User $user, array $filtros = []): Collection
    {
        $relation = $user->escalas()->with(['grupo', 'culto', 'evento']);

        if (! empty($filtros['status'])) {
            $relation->wherePivot('status', $filtros['status']);
        }

        if (! empty($filtros['from'])) {
            $relation->whereDate('data', '>=', $filtros['from']);
        }

        if (! empty($filtros['to'])) {
            $relation->whereDate('data', '<=', $filtros['to']);
        }

        return $relation->orderByDesc('data')->get();
    }
}
