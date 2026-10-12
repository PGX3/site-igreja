<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AniversariantesSemana;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoints consumidos por automações internas (ex.: agente que posta os
 * aniversariantes da semana no grupo de WhatsApp). Protegido por token estático.
 */
class AutomacaoController extends Controller
{
    public function aniversariantesSemana(Request $request): JsonResponse
    {
        $request->validate(['data' => ['nullable', 'date_format:Y-m-d']]);

        $referencia = $request->filled('data')
            ? Carbon::createFromFormat('Y-m-d', $request->input('data'))
            : Carbon::today();

        $inicio = AniversariantesSemana::inicioSemana($referencia);
        $lista = AniversariantesSemana::listar($referencia);

        return response()->json([
            'semana_inicio' => $inicio->format('Y-m-d'),
            'semana_fim' => $inicio->copy()->addDays(6)->format('Y-m-d'),
            'total' => $lista->count(),
            'aniversariantes' => $lista->map(fn ($a) => [
                'nome' => AniversariantesSemana::primeiroEUltimoNome($a['user']->name),
                'data' => $a['data']->format('Y-m-d'),
                'dia_semana' => AniversariantesSemana::DIAS_SEMANA[$a['data']->dayOfWeek],
            ])->values(),
            'mensagem' => $lista->isEmpty() ? null : AniversariantesSemana::mensagem($lista),
        ]);
    }
}
