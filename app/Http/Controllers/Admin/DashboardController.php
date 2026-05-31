<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Culto;
use App\Models\Escala;
use App\Models\Texto;
use App\Models\Sugestao;
use App\Models\PedidoOracao;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // ── Stats
        $stats = [
            'totalCultos'    => Culto::count(),
            'totalTextos'    => Texto::count(),
            'novasSugestoes' => Sugestao::where('lida', false)->count(),
            'novosPedidos'   => PedidoOracao::where('lido', false)->count(),
            'totalSugestoes' => Sugestao::count(),
            'totalPedidos'   => PedidoOracao::count(),
        ];

        // ── Próximo culto (calcula próxima data pelo dia_semana)
        $proximoCulto = null;
        $culto = Culto::where('ativo', true)->orderBy('id')->first();
        if ($culto) {
            $diasPt = [
                'Domingo' => 0, 'Segunda' => 1, 'Terça' => 2,
                'Quarta'  => 3, 'Quinta'  => 4, 'Sexta' => 5, 'Sábado' => 6,
            ];
            $diaAlvo = $diasPt[$culto->dia_semana] ?? 0;
            $hoje    = Carbon::today();
            $diff    = ($diaAlvo - (int) $hoje->format('w') + 7) % 7;
            $proxData = $hoje->copy()->addDays($diff ?: 7);

            $proximoCulto = [
                'id'         => $culto->id,
                'nome'       => $culto->nome,
                'dia_semana' => $culto->dia_semana,
                'horario'    => $culto->horario,
                'descricao'  => $culto->descricao,
                'dia'        => $proxData->day,
                'mes'        => mb_strtoupper($proxData->translatedFormat('M')),
                'data_fmt'   => $proxData->translatedFormat('l, d \d\e F'),
            ];
        }

        // ── Escalas: pastor/líder veem todas do grupo; membro vê só as suas
        if ($user->isMembro()) {
            $escalasProximas = collect([]);
            $minhasProximas  = $user->escalas()
                ->where('data', '>=', Carbon::today())
                ->orderBy('data')
                ->take(5)
                ->with('grupo')
                ->get()
                ->map(fn ($e) => [
                    'id'          => $e->id,
                    'titulo'      => $e->titulo,
                    'data'        => $e->data->format('Y-m-d'),
                    'dia'         => $e->data->day,
                    'dia_semana'  => mb_strtoupper(mb_substr($e->data->translatedFormat('D'), 0, 3)),
                    'hora_inicio' => substr($e->hora_inicio, 0, 5),
                    'grupo'       => $e->grupo?->only('id', 'nome'),
                    'status'      => $e->pivot->status,
                ]);
        } else {
            $minhasProximas  = collect([]);
            $escalasQuery    = Escala::with(['grupo', 'membros:id,name'])
                ->where('data', '>=', Carbon::today())
                ->orderBy('data')
                ->take(6);

            if ($user->isLider()) {
                $escalasQuery->where('grupo_id', $user->grupo_id);
            }

            $escalasProximas = $escalasQuery->get()->map(fn ($e) => [
                'id'          => $e->id,
                'titulo'      => $e->titulo,
                'data'        => $e->data->format('Y-m-d'),
                'dia'         => $e->data->day,
                'dia_semana'  => mb_strtoupper(mb_substr($e->data->translatedFormat('D'), 0, 3)),
                'hora_inicio' => substr($e->hora_inicio, 0, 5),
                'grupo'       => $e->grupo?->only('id', 'nome'),
                'membros'     => $e->membros->take(5)->map(fn ($m) => [
                    'id'       => $m->id,
                    'initials' => self::initials($m->name),
                    'color'    => self::avatarColor($m->name),
                ])->values(),
                'total_membros' => $e->membros->count(),
            ]);
        }

        return Inertia::render('Admin/Dashboard', array_merge($stats, [
            'proximoCulto'    => $proximoCulto,
            'escalasProximas' => $escalasProximas,
            'minhasProximas'  => $minhasProximas,
        ]));
    }

    private static function initials(string $name): string
    {
        $parts = array_filter(explode(' ', $name));
        if (!$parts) return '?';
        $first = $parts[array_key_first($parts)][0] ?? '';
        $last  = count($parts) > 1 ? $parts[array_key_last($parts)][0] ?? '' : '';
        return strtoupper($first . $last);
    }

    private static function avatarColor(string $name): string
    {
        $colors = ['#f97316','#3b82f6','#8b5cf6','#10b981','#ef4444','#f59e0b','#06b6d4','#ec4899'];
        return $colors[ord($name[0] ?? 'A') % count($colors)];
    }
}
