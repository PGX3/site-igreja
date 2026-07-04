<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Escala;
use Illuminate\Http\Request;

class EscalaAssetController extends Controller
{
    /**
     * Sobe um novo anexo: entra na biblioteca e é anexado a esta escala.
     */
    public function store(Request $request, Escala $escala)
    {
        $this->authorizeEscala($escala);

        $data = $request->validate([
            'tipo' => 'required|in:arquivo,imagem',
            'titulo' => 'nullable|string|max:200',
            'arquivo' => [
                'required',
                'file',
                $request->input('tipo') === 'imagem'
                    ? 'mimes:jpg,jpeg,png,webp|max:20480'
                    : 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:102400',
            ],
        ], [], ['arquivo' => 'arquivo']);

        $asset = AssetController::criarDeUpload($request, $data['tipo'], $data['titulo'] ?? null);
        $escala->assets()->syncWithoutDetaching([$asset->id]);

        return back()->with('success', 'Anexo adicionado!');
    }

    /**
     * Anexa anexos já existentes na biblioteca a esta escala.
     */
    public function attach(Request $request, Escala $escala)
    {
        $this->authorizeEscala($escala);

        $data = $request->validate([
            'asset_ids' => 'required|array',
            'asset_ids.*' => 'exists:assets,id',
        ]);

        $escala->assets()->syncWithoutDetaching($data['asset_ids']);

        return back()->with('success', 'Anexos vinculados!');
    }

    /**
     * Remove o vínculo com a escala (não apaga o anexo da biblioteca).
     */
    public function detach(Escala $escala, Asset $asset)
    {
        $this->authorizeEscala($escala);

        $escala->assets()->detach($asset->id);

        return back()->with('success', 'Anexo desvinculado.');
    }

    private function authorizeEscala(Escala $escala): void
    {
        abort_unless(auth()->user()->canManageGrupo($escala->grupo_id), 403);
    }
}
