<?php

namespace App\Http\Controllers;

use App\Models\Culto;
use App\Models\Texto;
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
}