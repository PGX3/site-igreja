<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Igreja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class IgrejaController extends Controller
{
    public function edit()
    {
        $igreja = Igreja::atual();

        return Inertia::render('Admin/Igreja/Form', [
            'igreja' => array_merge($igreja->toArray(), [
                'logo_url' => $igreja->logo_path ? Storage::disk('public')->url($igreja->logo_path) : null,
            ]),
        ]);
    }

    public function update(Request $request)
    {
        $igreja = Igreja::atual();

        $data = $request->validate([
            'nome' => 'required|string|max:150',
            'cnpj' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:120',
            'telefone' => 'nullable|string|max:40',
            'email' => 'nullable|email|max:150',
            'site' => 'nullable|string|max:150',
            'logo' => 'nullable|file|mimes:jpg,jpeg,png,webp,svg|max:5120',
            'remover_logo' => 'boolean',
        ], [], ['logo' => 'logo']);

        $update = collect($data)->only(['nome', 'cnpj', 'endereco', 'cidade', 'telefone', 'email', 'site'])->all();

        if ($request->boolean('remover_logo') && $igreja->logo_path) {
            Storage::disk('public')->delete($igreja->logo_path);
            $update['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($igreja->logo_path) {
                Storage::disk('public')->delete($igreja->logo_path);
            }
            $update['logo_path'] = $request->file('logo')->store('igreja', 'public');
        }

        $igreja->update($update);

        return back()->with('success', 'Dados da igreja atualizados!');
    }
}
