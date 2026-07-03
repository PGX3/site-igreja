<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Culto;
use App\Models\Escala;
use App\Models\Evento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalendarioController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Mês de referência (YYYY-MM), padrão: mês atual
        $ref = $request->query('mes');
        $inicio = $ref
            ? Carbon::createFromFormat('Y-m-d', $ref.'-01')->startOfMonth()
            : Carbon::today()->startOfMonth();
        $fim = $inicio->copy()->endOfMonth();

        $eventos = collect();

        // ── Escalas
        $escalasQuery = Escala::with('grupo')
            ->whereBetween('data', [$inicio->toDateString(), $fim->toDateString()]);
        if ($user->isLider()) {
            $escalasQuery->whereIn('grupo_id', $user->grupoIds());
        } elseif ($user->isMembro()) {
            $escalasQuery->whereIn('id', $user->escalas()->pluck('escalas.id'));
        }
        foreach ($escalasQuery->get() as $e) {
            $eventos->push([
                'date' => $e->data->format('Y-m-d'),
                'tipo' => 'escala',
                'titulo' => $e->titulo,
                'hora' => $e->hora_inicio ? substr($e->hora_inicio, 0, 5) : null,
                'href' => "/admin/escalas/{$e->id}",
            ]);
        }

        // ── Eventos
        foreach (Evento::where('ativo', true)
            ->whereBetween('data_evento', [$inicio->toDateString(), $fim->toDateString()])
            ->get() as $ev) {
            $eventos->push([
                'date' => $ev->data_evento->format('Y-m-d'),
                'tipo' => 'evento',
                'titulo' => $ev->nome,
                'hora' => $ev->horario ? substr($ev->horario, 0, 5) : null,
                'href' => "/eventos/{$ev->id}",
            ]);
        }

        // ── Cultos (recorrentes por dia da semana)
        $diasPt = [
            'Domingo' => 0, 'Segunda' => 1, 'Terça' => 2,
            'Quarta' => 3, 'Quinta' => 4, 'Sexta' => 5, 'Sábado' => 6,
        ];
        foreach (Culto::where('ativo', true)->get() as $culto) {
            $alvo = $diasPt[$culto->dia_semana] ?? null;
            if ($alvo === null) {
                continue;
            }
            $dia = $inicio->copy();
            while ($dia->lte($fim)) {
                if ((int) $dia->dayOfWeek === $alvo) {
                    $eventos->push([
                        'date' => $dia->format('Y-m-d'),
                        'tipo' => 'culto',
                        'titulo' => $culto->nome,
                        'hora' => $culto->horario ? substr($culto->horario, 0, 5) : null,
                        'href' => "/cultos/{$culto->id}",
                    ]);
                }
                $dia->addDay();
            }
        }

        return Inertia::render('Admin/Calendario', [
            'mes' => $inicio->format('Y-m'),
            'label' => ucfirst($inicio->translatedFormat('F Y')),
            'prev' => $inicio->copy()->subMonth()->format('Y-m'),
            'next' => $inicio->copy()->addMonth()->format('Y-m'),
            'hoje' => Carbon::today()->format('Y-m-d'),
            'eventos' => $eventos->sortBy('hora')->values(),
        ]);
    }
}
