<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecepcaoDispositivo;
use Illuminate\Http\Request;
use Inertia\Inertia;

/** Pastor gerencia os iPads da recepção: gera token, troca PIN, desliga. */
class RecepcaoController extends Controller
{
    public function index()
    {
        $dispositivos = RecepcaoDispositivo::orderBy('nome')
            ->get()
            ->map(fn (RecepcaoDispositivo $d) => [
                'id' => $d->id,
                'nome' => $d->nome,
                'tem_pin' => $d->temPin(),
                'cadastros' => $d->cadastros,
                'ultimo_uso_em' => optional($d->ultimo_uso_em)->toIso8601String(),
                'created_at' => optional($d->created_at)->format('Y-m-d'),
            ]);

        return Inertia::render('Admin/Recepcao/Index', [
            'dispositivos' => $dispositivos,
            'tokenNovo' => session('recepcao_token'),
            'apiUrl' => url('/api/v1/recepcao'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:60',
            'pin' => ['required', 'digits_between:4,8'],
        ], [
            'pin.required' => 'Defina um PIN para o modo diácono.',
            'pin.digits_between' => 'O PIN deve ter de 4 a 8 números.',
        ]);

        [$dispositivo, $token] = RecepcaoDispositivo::criar($data['nome'], $data['pin'], $request->user()->id);

        return redirect()->route('admin.recepcao.index')
            ->with('success', "Aparelho \"{$dispositivo->nome}\" criado. Copie o token agora: ele não aparece de novo.")
            ->with('recepcao_token', ['id' => $dispositivo->id, 'token' => $token]);
    }

    public function update(Request $request, RecepcaoDispositivo $dispositivo)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:60',
            'pin' => ['nullable', 'digits_between:4,8'],
        ], [
            'pin.digits_between' => 'O PIN deve ter de 4 a 8 números.',
        ]);

        $dispositivo->nome = $data['nome'];
        if (filled($data['pin'] ?? null)) {
            $dispositivo->definirPin($data['pin']);
        }
        $dispositivo->save();

        return back()->with('success', 'Aparelho atualizado!');
    }

    public function destroy(RecepcaoDispositivo $dispositivo)
    {
        $dispositivo->delete();

        return back()->with('success', 'Aparelho desligado. Ele não consegue mais cadastrar.');
    }
}
