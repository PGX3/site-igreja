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
        $tipo = $request->input('tipo');
        $ordem = ($aula->anexos()->max('ordem') ?? 0) + 1;

        if ($tipo === 'link') {
            $data = $request->validate([
                'titulo' => 'nullable|string|max:200',
                'url' => 'required|url|max:500',
            ], [], ['url' => 'link']);

            $aula->anexos()->create([
                'tipo' => 'link',
                'titulo' => $data['titulo'] ?? null,
                'url' => $data['url'],
                'ordem' => $ordem,
            ]);

            return back()->with('success', 'Link adicionado!');
        }

        if ($tipo === 'biblioteca') {
            $data = $request->validate([
                'asset_id' => 'required|exists:assets,id',
                'titulo' => 'nullable|string|max:200',
            ]);

            $aula->anexos()->create([
                'tipo' => 'arquivo',
                'asset_id' => $data['asset_id'],
                'titulo' => $data['titulo'] ?? null,
                'ordem' => $ordem,
            ]);

            return back()->with('success', 'Anexo da biblioteca adicionado!');
        }

        // Upload novo: cria um Asset na biblioteca e anexa (fica reutilizável)
        $request->validate([
            'titulo' => 'nullable|string|max:200',
            'arquivo' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,webp|max:102400',
        ], [], ['arquivo' => 'arquivo']);

        $ehImagem = str_starts_with((string) $request->file('arquivo')->getMimeType(), 'image/');
        $asset = AssetController::criarDeUpload($request, $ehImagem ? 'imagem' : 'arquivo', $request->input('titulo'));

        $aula->anexos()->create([
            'tipo' => 'arquivo',
            'asset_id' => $asset->id,
            'titulo' => $request->input('titulo'),
            'ordem' => $ordem,
        ]);

        return back()->with('success', 'Arquivo enviado e adicionado à biblioteca!');
    }

    public function destroy(AulaAnexo $anexo)
    {
        // Só apaga arquivo do disco se for um arquivo antigo salvo direto no anexo.
        // Arquivos da biblioteca (asset) continuam disponíveis para reuso.
        if (! $anexo->asset_id && $anexo->arquivo_path) {
            Storage::disk('public')->delete($anexo->arquivo_path);
        }

        $anexo->delete();

        return back()->with('success', 'Anexo removido.');
    }
}
