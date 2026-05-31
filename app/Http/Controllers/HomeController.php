<?php

namespace App\Http\Controllers;

use App\Models\Culto;
use App\Models\Texto;
use Carbon\Carbon;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $cultos = Culto::orderBy('id')->get();

        $textos = Texto::pluck('conteudo', 'chave');

        return Inertia::render('Home', [
            'cultos' => $cultos,
            'textos' => $textos,
        ]);
    }

    public function showCulto(Culto $culto)
    {
        $diasPt = [
            'Domingo' => 0, 'Segunda' => 1, 'Terça' => 2,
            'Quarta'  => 3, 'Quinta'  => 4, 'Sexta' => 5, 'Sábado' => 6,
        ];

        $meses = [
            1 => 'janeiro', 2 => 'fevereiro', 3 => 'março',    4 => 'abril',
            5 => 'maio',    6 => 'junho',     7 => 'julho',    8 => 'agosto',
            9 => 'setembro',10 => 'outubro', 11 => 'novembro', 12 => 'dezembro',
        ];

        $diasNomes = [
            0 => 'domingo',       1 => 'segunda-feira', 2 => 'terça-feira',
            3 => 'quarta-feira',  4 => 'quinta-feira',  5 => 'sexta-feira',
            6 => 'sábado',
        ];

        $diaAlvo  = $diasPt[$culto->dia_semana] ?? 0;
        $hoje     = Carbon::today();
        $diff     = ($diaAlvo - (int) $hoje->format('w') + 7) % 7;
        $proxData = $hoje->copy()->addDays($diff ?: 7);

        $nomeMes = $meses[$proxData->month];
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
}