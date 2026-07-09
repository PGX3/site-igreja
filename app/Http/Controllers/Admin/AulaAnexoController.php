<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aula;
use App\Models\AulaAnexo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AulaAnexoController extends Controller
{
    public function store(Request $request, Aula $aula)
    {
        $data = $request->validate([
            'tipo' => 'required|in:arquivo,link',
            'titulo' => 'nullable|string|max:200',
            'arquivo' => $request->input('tipo') === 'arquivo'
                ? 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,webp|max:102400'
                : 'nullable',
            'url' => $request->input('tipo') === 'link' ? 'required|url|max:500' : 'nullable',
        ], [], ['arquivo' => 'arquivo', 'url' => 'link']);

        $anexo = [
            'tipo' => $data['tipo'],
            'titulo' => $data['titulo'] ?? null,
            'ordem' => ($aula->anexos()->max('ordem') ?? 0) + 1,
        ];

        if ($data['tipo'] === 'arquivo') {
            $anexo['arquivo_path'] = $request->file('arquivo')->store('cursos/anexos', 'public');
            $anexo['arquivo_nome'] = $request->file('arquivo')->getClientOriginalName();
        } else {
            $anexo['url'] = $data['url'];
        }

        $aula->anexos()->create($anexo);

        return back()->with('success', 'Anexo adicionado!');
    }

    public function destroy(AulaAnexo $anexo)
    {
        if ($anexo->arquivo_path) {
            Storage::disk('public')->delete($anexo->arquivo_path);
        }

        $anexo->delete();

        return back()->with('success', 'Anexo removido.');
    }
}
