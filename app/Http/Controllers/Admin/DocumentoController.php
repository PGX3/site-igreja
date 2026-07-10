<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Documento;
use App\Models\DocumentoTemplate;
use App\Models\Igreja;
use App\Services\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DocumentoController extends Controller
{
    public function index()
    {
        $documentos = Documento::with('template:id,nome', 'createdBy:id,name')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'titulo' => $d->titulo,
                'template' => $d->template?->only('id', 'nome'),
                'created_by' => $d->createdBy?->only('id', 'name'),
                'created_at' => $d->created_at->format('Y-m-d'),
            ]);

        return Inertia::render('Admin/Documentos/Index', [
            'documentos' => $documentos,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Documentos/Form', [
            'documento' => null,
            'templates' => $this->templatesAtivos(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['created_by'] = auth()->id();

        Documento::create($data);

        return redirect()->route('admin.documentos.index')->with('success', 'Documento criado!');
    }

    public function edit(Documento $documento)
    {
        return Inertia::render('Admin/Documentos/Form', [
            'documento' => [
                'id' => $documento->id,
                'titulo' => $documento->titulo,
                'corpo' => $documento->corpo,
                'documento_template_id' => $documento->documento_template_id,
                'valores' => $documento->valores ?? [],
            ],
            'templates' => $this->templatesAtivos(),
        ]);
    }

    public function update(Request $request, Documento $documento)
    {
        $data = $this->validateData($request);

        $documento->update($data);

        return redirect()->route('admin.documentos.index')->with('success', 'Documento atualizado!');
    }

    public function destroy(Documento $documento)
    {
        $documento->delete();

        return redirect()->route('admin.documentos.index')->with('success', 'Documento removido!');
    }

    public function imprimir(Documento $documento)
    {
        $igreja = Igreja::atual();

        return view('print.documento', [
            'documento' => $documento,
            'igreja' => $igreja,
            'logoUrl' => $igreja->logo_path ? Storage::disk('public')->url($igreja->logo_path) : null,
        ]);
    }

    private function templatesAtivos()
    {
        return DocumentoTemplate::where('ativo', true)
            ->orderBy('nome')
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'nome' => $t->nome,
                'corpo' => $t->corpo,
                'variaveis' => $t->variaveis ?? [],
            ]);
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'documento_template_id' => 'nullable|exists:documento_templates,id',
            'titulo' => 'required|string|max:200',
            'corpo' => 'nullable|string',
            'valores' => 'nullable|array',
        ]);

        $data['valores'] = $data['valores'] ?? [];
        $data['corpo'] = HtmlSanitizer::clean($data['corpo'] ?? null);

        return $data;
    }
}
