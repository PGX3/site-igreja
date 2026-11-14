<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PulpitoDispositivo;
use Illuminate\Http\Request;
use Inertia\Inertia;

/** Pastor gerencia os iPads do púlpito (app Pregação): gera token e desliga. */
class PulpitoController extends Controller
{
    public function index()
    {
        $dispositivos = PulpitoDispositivo::orderBy('nome')
            ->get()
            ->map(fn (PulpitoDispositivo $d) => [
                'id' => $d->id,
                'nome' => $d->nome,
                'ultimo_uso_em' => optional($d->ultimo_uso_em)->toIso8601String(),
                'created_at' => optional($d->created_at)->format('Y-m-d'),
            ]);

        return Inertia::render('Admin/Pulpito/Index', [
            'dispositivos' => $dispositivos,
            'tokenNovo' => session('pulpito_token'),
            'apiUrl' => url('/api/v1/pulpito'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['nome' => 'required|string|max:60']);

        [$dispositivo, $token] = PulpitoDispositivo::criar($data['nome'], $request->user()->id);

        return redirect()->route('admin.pulpito.index')
            ->with('success', "Aparelho \"{$dispositivo->nome}\" criado. Copie o token agora: ele não aparece de novo.")
            ->with('pulpito_token', ['id' => $dispositivo->id, 'token' => $token]);
    }

    public function destroy(PulpitoDispositivo $dispositivo)
    {
        $dispositivo->delete();

        return back()->with('success', 'Aparelho desligado. Ele não consegue mais baixar roteiros.');
    }
}
