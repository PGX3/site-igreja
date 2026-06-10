<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventoController extends Controller
{
    public function index()
    {
        $eventos = Evento::orderBy('data_evento')->get()->map(fn ($e) => [
            'id'          => $e->id,
            'nome'        => $e->nome,
            'data_evento' => $e->data_evento->format('Y-m-d'),
            'horario'     => $e->horario,
            'local'       => $e->local,
            'descricao'   => $e->descricao,
            'ativo'       => $e->ativo,
        ]);

        return Inertia::render('Admin/Eventos/Index', [
            'eventos' => $eventos,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Eventos/Form', ['evento' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'      => 'required|string|max:120',
            'data_evento' => 'required|date',
            'horario'   => 'nullable|string|max:20',
            'local'     => 'nullable|string|max:120',
            'descricao' => 'nullable|string',
            'ativo'     => 'boolean',
        ]);

        Evento::create($data);
        return redirect()->route('admin.eventos.index')->with('success', 'Evento criado!');
    }

    public function edit(Evento $evento)
    {
        return Inertia::render('Admin/Eventos/Form', [
            'evento' => array_merge($evento->toArray(), [
                'data_evento' => $evento->data_evento->format('Y-m-d'),
            ]),
        ]);
    }

    public function update(Request $request, Evento $evento)
    {
        $data = $request->validate([
            'nome'      => 'required|string|max:120',
            'data_evento' => 'required|date',
            'horario'   => 'nullable|string|max:20',
            'local'     => 'nullable|string|max:120',
            'descricao' => 'nullable|string',
            'ativo'     => 'boolean',
        ]);

        $evento->update($data);
        return redirect()->route('admin.eventos.index')->with('success', 'Evento atualizado!');
    }

    public function destroy(Evento $evento)
    {
        $evento->delete();
        return redirect()->route('admin.eventos.index')->with('success', 'Evento removido!');
    }
}
