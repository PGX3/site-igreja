<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Culto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CultoController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Cultos/Index', [
            'cultos' => Culto::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Cultos/Form', ['culto' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'       => 'required|string|max:100',
            'dia_semana' => 'required|string',
            'horario'    => 'required|string',
            'descricao'  => 'nullable|string',
            'ativo'      => 'boolean',
        ]);

        Culto::create($data);
        return redirect()->route('admin.cultos.index')->with('success', 'Culto criado!');
    }

    public function edit(Culto $culto)
    {
        return Inertia::render('Admin/Cultos/Form', ['culto' => $culto]);
    }

    public function update(Request $request, Culto $culto)
    {
        $data = $request->validate([
            'nome'       => 'required|string|max:100',
            'dia_semana' => 'required|string',
            'horario'    => 'required|string',
            'descricao'  => 'nullable|string',
            'ativo'      => 'boolean',
        ]);

        $culto->update($data);
        return redirect()->route('admin.cultos.index')->with('success', 'Culto atualizado!');
    }

    public function destroy(Culto $culto)
    {
        $culto->delete();
        return redirect()->route('admin.cultos.index')->with('success', 'Culto removido!');
    }
}
