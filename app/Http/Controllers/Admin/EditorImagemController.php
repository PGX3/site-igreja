<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EditorImagemController extends Controller
{
    /**
     * Recebe uma imagem colada/enviada pelo editor de aulas e devolve a URL pública.
     */
    public function store(Request $request)
    {
        $request->validate([
            'imagem' => 'required|file|mimes:jpg,jpeg,png,webp|max:20480',
        ], [], ['imagem' => 'imagem']);

        $path = $request->file('imagem')->store('cursos', 'public');

        return response()->json([
            'url' => Storage::url($path),
        ]);
    }
}
