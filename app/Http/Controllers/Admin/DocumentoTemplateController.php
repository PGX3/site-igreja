<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentoTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DocumentoTemplateController extends Controller
{
    public function index()
    {
        $templates = DocumentoTemplate::withCount('documentos')
            ->orderBy('nome')
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'nome' => $t->nome,
                'descricao' => $t->descricao,
                'variaveis' => $t->variaveis ?? [],
                'documentos_count' => $t->documentos_count,
                'ativo' => $t->ativo,
            ]);

        return Inertia::render('Admin/DocumentoTemplates/Index', [
            'templates' => $templates,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/DocumentoTemplates/Form', ['template' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['created_by'] = auth()->id();

        DocumentoTemplate::create($data);

        return redirect()->route('admin.documento-templates.index')->with('success', 'Modelo criado!');
    }

    public function edit(DocumentoTemplate $documentoTemplate)
    {
        return Inertia::render('Admin/DocumentoTemplates/Form', [
            'template' => [
                'id' => $documentoTemplate->id,
                'nome' => $documentoTemplate->nome,
                'descricao' => $documentoTemplate->descricao,
                'corpo' => $documentoTemplate->corpo,
                'variaveis' => $documentoTemplate->variaveis ?? [],
                'ativo' => $documentoTemplate->ativo,
            ],
        ]);
    }

    public function update(Request $request, DocumentoTemplate $documentoTemplate)
    {
        $data = $this->validateData($request);

        $documentoTemplate->update($data);

        return redirect()->route('admin.documento-templates.index')->with('success', 'Modelo atualizado!');
    }

    public function destroy(DocumentoTemplate $documentoTemplate)
    {
        $documentoTemplate->delete();

        return redirect()->route('admin.documento-templates.index')->with('success', 'Modelo removido!');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'nome' => 'required|string|max:150',
            'descricao' => 'nullable|string|max:500',
            'corpo' => 'nullable|string',
            'variaveis' => 'nullable|array',
            'variaveis.*.chave' => 'required|string|max:60',
            'variaveis.*.label' => 'required|string|max:120',
            'variaveis.*.tipo' => 'required|in:texto,data,multilinha',
            'ativo' => 'boolean',
        ]);

        $data['variaveis'] = $data['variaveis'] ?? [];

        return $data;
    }
}
