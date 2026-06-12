<?php

namespace App\Http\Controllers;

use App\Models\Culto;
use App\Models\Evento;
use App\Models\Texto;
use App\Services\CultoProximaDataResolver;
use Carbon\Carbon;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $cultos = Culto::orderBy('id')->get();

        $eventos = Evento::where('ativo', true)
            ->whereDate('data_evento', '>=', Carbon::today())
            ->orderBy('data_evento')
            ->get()
            ->map(fn ($e) => [
                'id'          => $e->id,
                'nome'        => $e->nome,
                'data_evento' => $e->data_evento->format('Y-m-d'),
                'horario'     => $e->horario,
                'local'       => $e->local,
                'descricao'   => $e->descricao,
                'dia'         => $e->data_evento->day,
                'mes_curto'   => mb_strtoupper(self::mesesCurtos()[$e->data_evento->month]),
            ]);

        $textos = Texto::pluck('conteudo', 'chave');

        return Inertia::render('Home', [
            'cultos'  => $cultos,
            'eventos' => $eventos,
            'textos'  => $textos,
        ]);
    }

    public function showCulto(Culto $culto, CultoProximaDataResolver $resolver)
    {
        $meses     = self::meses();
        $diasNomes = self::diasNomes();

        $proxData = $resolver->resolve($culto);
        $nomeMes  = $meses[$proxData->month];
        $nomeDia = $diasNomes[$proxData->dayOfWeek];

        return Inertia::render('CultoDetalhe', [
            'culto' => [
                'id'         => $culto->id,
                'nome'       => $culto->nome,
                'dia_semana' => $culto->dia_semana,
                'horario'    => $culto->horario,
                'descricao'  => $culto->descricao,
                'ativo'      => $culto->ativo,
                'dia'        => $proxData->day,
                'mes'        => mb_strtoupper($nomeMes),
                'ano'        => $proxData->year,
                'data_fmt'   => ucfirst($nomeDia) . ', ' . $proxData->day . ' de ' . $nomeMes . ' de ' . $proxData->year,
            ],
        ]);
    }

    public function showEvento(Evento $evento)
    {
        $meses     = self::meses();
        $diasNomes = self::diasNomes();

        $dataEvento = $evento->data_evento;
        $nomeMes    = $meses[$dataEvento->month];
        $nomeDia    = $diasNomes[$dataEvento->dayOfWeek];

        return Inertia::render('EventoDetalhe', [
            'evento' => [
                'id'        => $evento->id,
                'nome'      => $evento->nome,
                'horario'   => $evento->horario,
                'local'     => $evento->local,
                'descricao' => $evento->descricao,
                'ativo'     => $evento->ativo,
                'dia'       => $dataEvento->day,
                'mes'       => mb_strtoupper($nomeMes),
                'ano'       => $dataEvento->year,
                'data_fmt'  => ucfirst($nomeDia) . ', ' . $dataEvento->day . ' de ' . $nomeMes . ' de ' . $dataEvento->year,
                'passado'   => $dataEvento->lt(Carbon::today()),
            ],
        ]);
    }

    private static function meses(): array
    {
        return [
            1 => 'janeiro', 2 => 'fevereiro', 3 => 'março',    4 => 'abril',
            5 => 'maio',    6 => 'junho',     7 => 'julho',    8 => 'agosto',
            9 => 'setembro',10 => 'outubro', 11 => 'novembro', 12 => 'dezembro',
        ];
    }

    private static function mesesCurtos(): array
    {
        return [
            1 => 'jan', 2 => 'fev', 3 => 'mar', 4 => 'abr',
            5 => 'mai', 6 => 'jun', 7 => 'jul', 8 => 'ago',
            9 => 'set', 10 => 'out', 11 => 'nov', 12 => 'dez',
        ];
    }

    private static function diasNomes(): array
    {
        return [
            0 => 'domingo',       1 => 'segunda-feira', 2 => 'terça-feira',
            3 => 'quarta-feira',  4 => 'quinta-feira',  5 => 'sexta-feira',
            6 => 'sábado',
        ];
    }
}
