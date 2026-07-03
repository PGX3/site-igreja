<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Inertia\Inertia;

class AniversarioController extends Controller
{
    public function index()
    {
        $hoje = Carbon::today();

        $meses = [
            1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril', 5 => 'maio', 6 => 'junho',
            7 => 'julho', 8 => 'agosto', 9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro',
        ];

        $diasSemanaNome = [
            0 => 'domingo', 1 => 'segunda-feira', 2 => 'terça-feira', 3 => 'quarta-feira',
            4 => 'quinta-feira', 5 => 'sexta-feira', 6 => 'sábado',
        ];

        // Datas da semana atual (segunda a domingo)
        $inicioSemana = $hoje->copy()->startOfWeek(Carbon::MONDAY);
        $diasDaSemana = collect(range(0, 6))->map(fn ($i) => $inicioSemana->copy()->addDays($i));

        $aniversariantesSemana = User::whereNotNull('data_nascimento')
            ->where('is_superadmin', false)
            ->get()
            ->map(function ($u) use ($diasDaSemana, $meses, $diasSemanaNome, $hoje) {
                $nasc = $u->data_nascimento;
                $match = $diasDaSemana->first(fn ($d) => $d->month === $nasc->month && $d->day === $nasc->day);
                if (! $match) {
                    return null;
                }

                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'telefone' => $u->telefone,
                    'initials' => self::initials($u->name),
                    'color' => self::avatarColor($u->name),
                    'data_fmt' => $nasc->day.' de '.$meses[$nasc->month],
                    'dia_semana' => $diasSemanaNome[$match->dayOfWeek],
                    'dia_semana_ordem' => $match->dayOfWeek === 0 ? 7 : $match->dayOfWeek,
                    'dia' => $match->day,
                    'idade' => $match->year - $nasc->year,
                    'hoje' => $match->isSameDay($hoje),
                    'ja_passou' => $match->lt($hoje),
                ];
            })
            ->filter()
            ->sortBy('dia_semana_ordem')
            ->values();

        $comAniversario = User::whereNotNull('data_nascimento')
            ->where('is_superadmin', false)
            ->get()
            ->map(function ($u) use ($hoje, $meses) {
                $nasc = $u->data_nascimento;
                $proxAniv = Carbon::create($hoje->year, $nasc->month, $nasc->day);
                if ($proxAniv->lt($hoje)) {
                    $proxAniv->addYear();
                }

                $dias = (int) $hoje->diffInDays($proxAniv);
                $idade = $proxAniv->year - $nasc->year;

                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'telefone' => $u->telefone,
                    'initials' => self::initials($u->name),
                    'color' => self::avatarColor($u->name),
                    'data_fmt' => $nasc->day.' de '.$meses[$nasc->month],
                    'data_iso' => $nasc->format('Y-m-d'),
                    'dias_restantes' => $dias,
                    'idade' => $idade,
                    'hoje' => $dias === 0,
                    'esta_semana' => $dias <= 7,
                    'mes_atual' => $proxAniv->month,
                    'dia_atual' => $proxAniv->day,
                ];
            })
            ->sortBy('dias_restantes')
            ->values();

        $semAniversario = User::whereNull('data_nascimento')
            ->where('is_superadmin', false)
            ->orderBy('name')
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'initials' => self::initials($u->name),
                'color' => self::avatarColor($u->name),
            ]);

        return Inertia::render('Admin/Aniversarios', [
            'comAniversario' => $comAniversario,
            'semAniversario' => $semAniversario,
            'aniversariantesSemana' => $aniversariantesSemana,
        ]);
    }

    private static function initials(string $name): string
    {
        $parts = array_filter(explode(' ', $name));
        if (! $parts) {
            return '?';
        }
        $first = $parts[array_key_first($parts)][0] ?? '';
        $last = count($parts) > 1 ? $parts[array_key_last($parts)][0] ?? '' : '';

        return strtoupper($first.$last);
    }

    private static function avatarColor(string $name): string
    {
        $colors = ['#f97316', '#3b82f6', '#8b5cf6', '#10b981', '#ef4444', '#f59e0b', '#06b6d4', '#ec4899'];

        return $colors[ord($name[0] ?? 'A') % count($colors)];
    }
}
