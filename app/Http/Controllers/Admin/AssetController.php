<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AssetController extends Controller
{
    public function index()
    {
        $assets = Asset::with('createdBy:id,name')
            ->latest()
            ->get()
            ->map(fn ($a) => $this->toArray($a));

        return Inertia::render('Admin/Assets/Index', compact('assets'));
    }

    public function create()
    {
        return Inertia::render('Admin/Assets/Form');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        self::criarDeUpload($request, $data['tipo'], $data['titulo'] ?? null);

        return redirect()->route('admin.assets.index')->with('success', 'Anexo adicionado!');
    }

    public function edit(Asset $asset)
    {
        return Inertia::render('Admin/Assets/Form', [
            'asset' => $this->toArray($asset),
        ]);
    }

    public function update(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'tipo' => 'required|in:arquivo,imagem',
            'titulo' => 'nullable|string|max:200',
            'arquivo' => $this->fileRules($request, false),
        ], [], ['arquivo' => 'arquivo']);

        $update = ['tipo' => $data['tipo'], 'titulo' => $data['titulo'] ?? null];

        if ($request->hasFile('arquivo')) {
            Storage::disk('public')->delete($asset->arquivo_path);
            $update['arquivo_path'] = $request->file('arquivo')->store('assets', 'public');
            $update['arquivo_nome'] = $request->file('arquivo')->getClientOriginalName();
        }

        $asset->update($update);

        return redirect()->route('admin.assets.index')->with('success', 'Anexo atualizado!');
    }

    public function destroy(Asset $asset)
    {
        Storage::disk('public')->delete($asset->arquivo_path);
        $asset->delete();

        return redirect()->route('admin.assets.index')->with('success', 'Anexo removido.');
    }

    public function download(Asset $asset)
    {
        return Storage::disk('public')->download($asset->arquivo_path, $asset->arquivo_nome);
    }

    /**
     * Cria um Asset na biblioteca a partir de um upload. Reutilizado ao subir direto na escala.
     */
    public static function criarDeUpload(Request $request, string $tipo, ?string $titulo = null): Asset
    {
        return Asset::create([
            'created_by' => auth()->id(),
            'tipo' => $tipo,
            'titulo' => $titulo,
            'arquivo_path' => $request->file('arquivo')->store('assets', 'public'),
            'arquivo_nome' => $request->file('arquivo')->getClientOriginalName(),
        ]);
    }

    private function toArray(Asset $a): array
    {
        return [
            'id' => $a->id,
            'tipo' => $a->tipo,
            'titulo' => $a->titulo,
            'arquivo_path' => $a->arquivo_path,
            'arquivo_nome' => $a->arquivo_nome,
            'created_by' => $a->createdBy?->only('id', 'name'),
            'created_at' => $a->created_at->format('d/m/Y'),
        ];
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'tipo' => 'required|in:arquivo,imagem',
            'titulo' => 'nullable|string|max:200',
            'arquivo' => $this->fileRules($request, true),
        ], [], ['arquivo' => 'arquivo']);
    }

    private function fileRules(Request $request, bool $required): array
    {
        $rules = $required ? ['required'] : ['nullable'];
        $rules[] = 'file';
        $rules[] = $request->input('tipo') === 'imagem'
            ? 'mimes:jpg,jpeg,png,webp|max:20480'
            : 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:102400';

        return $rules;
    }
}
