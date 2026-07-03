<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Texto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TextoController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Textos/Index', [
            'textos' => Texto::all(),
        ]);
    }

    public function edit(Texto $texto)
    {
        return Inertia::render('Admin/Textos/Form', ['texto' => $texto]);
    }

    public function update(Request $request, Texto $texto)
    {
        $data = $request->validate([
            'conteudo' => 'required|string',
        ]);

        $texto->update($data);

        return redirect()->route('admin.textos.index')->with('success', 'Texto atualizado!');
    }
}
