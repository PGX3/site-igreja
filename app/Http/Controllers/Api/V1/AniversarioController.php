<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AniversariantesSemana;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AniversarioController extends Controller
{
    /**
     * Aniversariantes para o app (pastor + líder, mesma regra do painel).
     * `semana`: segunda a domingo da data de referência (mesma regra da automação do WhatsApp).
     * `proximos`: todos com data de nascimento, ordenados pelo próximo aniversário.
     * `sem_data`: quem ainda não tem data de nascimento cadastrada.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate(['data' => ['nullable', 'date_format:Y-m-d']]);

        $hoje = $request->filled('data')
            ? Carbon::createFromFormat('Y-m-d', $request->input('data'))->startOfDay()
            : Carbon::today();

        $semana = AniversariantesSemana::listar($hoje)
            ->map(function ($a) use ($hoje) {
                $u = $a['user'];
                $data = $a['data'];

                return [
                    'id' => $u->id,
                    'nome' => $u->name,
                    'telefone' => $u->telefone,
                    'data_nascimento' => $u->data_nascimento->format('Y-m-d'),
                    'data' => $data->format('Y-m-d'),
                    'dia_semana' => AniversariantesSemana::DIAS_SEMANA[$data->dayOfWeek],
                    'idade' => $data->year - $u->data_nascimento->year,
                    'hoje' => $data->isSameDay($hoje),
                    'ja_passou' => $data->lt($hoje),
                ];
            })
            ->values();

        $proximos = User::whereNotNull('data_nascimento')
            ->where('is_superadmin', false)
            ->get()
            ->map(function (User $u) use ($hoje) {
                $nasc = $u->data_nascimento;
                $proximo = Carbon::create($hoje->year, $nasc->month, $nasc->day);
                if ($proximo->lt($hoje)) {
                    $proximo->addYear();
                }
                $dias = (int) $hoje->diffInDays($proximo);

                return [
                    'id' => $u->id,
                    'nome' => $u->name,
                    'telefone' => $u->telefone,
                    'data_nascimento' => $nasc->format('Y-m-d'),
                    'proximo_aniversario' => $proximo->format('Y-m-d'),
                    'dias_restantes' => $dias,
                    'idade' => $proximo->year - $nasc->year,
                    'hoje' => $dias === 0,
                ];
            })
            ->sortBy('dias_restantes')
            ->values();

        $semData = User::whereNull('data_nascimento')
            ->where('is_superadmin', false)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $u) => ['id' => $u->id, 'nome' => $u->name]);

        return response()->json([
            'data' => [
                'semana_inicio' => AniversariantesSemana::inicioSemana($hoje)->format('Y-m-d'),
                'semana' => $semana,
                'proximos' => $proximos,
                'sem_data' => $semData,
            ],
        ]);
    }
}
