<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Familia;
use App\Models\User;
use App\Support\Cpf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class FamiliaController extends Controller
{
    public function index(Request $request)
    {
        $busca = trim((string) $request->query('busca', ''));

        $familias = Familia::query()
            ->with(['responsavel:id,name', 'membros:id,name,familia_id'])
            ->withCount('membros')
            ->when($busca !== '', fn($q) =>
                $q->where(fn($w) => $w
                    ->where('cidade', 'like', "%{$busca}%")
                    ->orWhereHas('membros', fn($m) => $m->where('name', 'like', "%{$busca}%"))))
            ->get()
            ->map(fn($f) => [
                'id'                 => $f->id,
                'nome'               => $f->nome,
                'cidade'             => $f->cidade,
                'uf'                 => $f->uf,
                'telefone_principal' => $f->telefone_principal,
                'responsavel'        => $f->responsavel?->name,
                'membros_count'      => $f->membros_count,
            ])
            ->sortBy('nome')
            ->values();

        return Inertia::render('Admin/Familias/Index', [
            'familias' => $familias,
            'busca'    => $busca,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Familias/Form', [
            'pessoas_disponiveis' => $this->pessoasDisponiveis(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $this->garantirUnicoResponsavel($data['pessoas']);

        $familia = DB::transaction(function () use ($data) {
            $familia = Familia::create([
                'endereco'           => $data['endereco'],
                'cidade'             => $data['cidade'],
                'uf'                 => strtoupper($data['uf']),
                'cep'                => $data['cep'],
                'telefone_principal' => $data['telefone_principal'] ?? null,
                'observacoes'        => $data['observacoes'] ?? null,
            ]);

            $responsavelId = null;
            foreach ($data['pessoas'] as $p) {
                $attrs = [
                    'name'            => $p['name'],
                    'telefone'        => $p['telefone'],
                    'email'           => $p['email'] ?? null,
                    'data_nascimento' => $p['data_nascimento'] ?? null,
                    'sexo'            => $p['sexo'] ?? null,
                    'estado_civil'    => $p['estado_civil'] ?? null,
                    'cpf'             => $p['cpf'] ?? null,
                    'tipo'            => $p['tipo'],
                    'batizado_aguas'  => $p['batizado_aguas'] ?? null,
                    'familia_id'      => $familia->id,
                ];

                if (!empty($p['id'])) {
                    $user = User::find($p['id']);
                    if (!$user || $user->familia_id !== null) {
                        continue;
                    }
                    $user->update($attrs);
                } else {
                    $user = User::create($attrs);
                }

                if (!empty($p['is_responsavel'])) {
                    $responsavelId = $user->id;
                }
            }

            $familia->update(['responsavel_id' => $responsavelId]);

            return $familia;
        });

        return redirect()->route('admin.familias.show', $familia)
            ->with('success', 'Família cadastrada!');
    }

    public function show(Familia $familia)
    {
        $familia->load(['responsavel:id,name', 'membros' => fn($q) => $q->orderBy('name')]);

        return Inertia::render('Admin/Familias/Show', [
            'familia' => [
                'id'                 => $familia->id,
                'nome'               => $familia->nome,
                'endereco'           => $familia->endereco,
                'cidade'             => $familia->cidade,
                'uf'                 => $familia->uf,
                'cep'                => $familia->cep,
                'telefone_principal' => $familia->telefone_principal,
                'observacoes'        => $familia->observacoes,
                'responsavel_id'     => $familia->responsavel_id,
                'responsavel'        => $familia->responsavel?->name,
                'endereco_completo'  => $familia->enderecoCompleto(),
                'membros' => $familia->membros->map(fn($u) => [
                    'id'              => $u->id,
                    'name'            => $u->name,
                    'telefone'        => $u->telefone,
                    'tipo'            => $u->tipo,
                    'data_nascimento' => optional($u->data_nascimento)->format('Y-m-d'),
                    'batizado_aguas'  => $u->batizado_aguas,
                    'is_responsavel'  => $u->id === $familia->responsavel_id,
                ])->values(),
            ],
        ]);
    }

    public function edit(Familia $familia)
    {
        $familia->load(['membros' => fn($q) => $q->orderBy('id')]);

        return Inertia::render('Admin/Familias/Form', [
            'familia' => [
                'id'                 => $familia->id,
                'nome'               => $familia->nome,
                'endereco'           => $familia->endereco,
                'cidade'             => $familia->cidade,
                'uf'                 => $familia->uf,
                'cep'                => $familia->cep,
                'telefone_principal' => $familia->telefone_principal,
                'observacoes'        => $familia->observacoes,
                'responsavel_id'     => $familia->responsavel_id,
                'pessoas' => $familia->membros->map(fn($u) => [
                    'id'              => $u->id,
                    'name'            => $u->name,
                    'telefone'        => $u->telefone,
                    'email'           => $u->email,
                    'data_nascimento' => optional($u->data_nascimento)->format('Y-m-d'),
                    'sexo'            => $u->sexo,
                    'estado_civil'    => $u->estado_civil,
                    'cpf'             => $u->cpf,
                    'tipo'            => $u->tipo,
                    'batizado_aguas'  => $u->batizado_aguas,
                    'is_responsavel'  => $u->id === $familia->responsavel_id,
                ])->values(),
            ],
            'pessoas_disponiveis' => $this->pessoasDisponiveis(),
        ]);
    }

    public function update(Request $request, Familia $familia)
    {
        $data = $this->validateData($request);
        $this->garantirUnicoResponsavel($data['pessoas']);

        DB::transaction(function () use ($familia, $data) {
            $familia->update([
                'endereco'           => $data['endereco'],
                'cidade'             => $data['cidade'],
                'uf'                 => strtoupper($data['uf']),
                'cep'                => $data['cep'],
                'telefone_principal' => $data['telefone_principal'] ?? null,
                'observacoes'        => $data['observacoes'] ?? null,
            ]);

            $idsRecebidos = [];
            $responsavelId = null;

            foreach ($data['pessoas'] as $p) {
                $attrs = [
                    'name'            => $p['name'],
                    'telefone'        => $p['telefone'],
                    'email'           => $p['email'] ?? null,
                    'data_nascimento' => $p['data_nascimento'] ?? null,
                    'sexo'            => $p['sexo'] ?? null,
                    'estado_civil'    => $p['estado_civil'] ?? null,
                    'cpf'             => $p['cpf'] ?? null,
                    'tipo'            => $p['tipo'],
                    'batizado_aguas'  => $p['batizado_aguas'] ?? null,
                    'familia_id'      => $familia->id,
                ];

                if (!empty($p['id'])) {
                    $user = User::find($p['id']);
                    if ($user && ($user->familia_id === null || $user->familia_id === $familia->id)) {
                        $user->update($attrs);
                        $idsRecebidos[] = $user->id;
                    } else {
                        continue;
                    }
                } else {
                    $user = User::create($attrs);
                    $idsRecebidos[] = $user->id;
                }

                if (!empty($p['is_responsavel'])) {
                    $responsavelId = $user->id;
                }
            }

            User::where('familia_id', $familia->id)
                ->whereNotIn('id', $idsRecebidos)
                ->update(['familia_id' => null]);

            $familia->update(['responsavel_id' => $responsavelId]);
        });

        return redirect()->route('admin.familias.show', $familia)
            ->with('success', 'Família atualizada!');
    }

    public function destroy(Familia $familia)
    {
        DB::transaction(function () use ($familia) {
            User::where('familia_id', $familia->id)->update(['familia_id' => null]);
            $familia->update(['responsavel_id' => null]);
            $familia->delete();
        });

        return redirect()->route('admin.familias.index')
            ->with('success', 'Família removida. Os integrantes continuam cadastrados como avulsos.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'endereco'           => 'required|string|max:255',
            'cidade'             => 'required|string|max:80',
            'uf'                 => 'required|string|size:2',
            'cep'                => 'required|string|max:10',
            'telefone_principal' => 'nullable|string|max:20',
            'observacoes'        => 'nullable|string|max:2000',

            'pessoas'                   => 'required|array|min:1',
            'pessoas.*.id'              => 'nullable|integer|exists:users,id',
            'pessoas.*.name'            => 'required|string|max:100',
            'pessoas.*.telefone'        => 'required|string|max:20',
            'pessoas.*.email'           => 'nullable|email|distinct',
            'pessoas.*.cpf'             => 'nullable|string|max:14|distinct',
            'pessoas.*.data_nascimento' => 'required|date|before:today',
            'pessoas.*.sexo'            => 'nullable|in:M,F',
            'pessoas.*.estado_civil'    => 'nullable|string|max:30',
            'pessoas.*.tipo'            => 'required|in:membro,visitante',
            'pessoas.*.batizado_aguas'  => 'required|boolean',
            'pessoas.*.is_responsavel'  => 'nullable|boolean',
        ]);

        $erros = [];
        foreach ($data['pessoas'] as $i => $p) {
            $ignoreId = $p['id'] ?? null;

            if (!empty($p['email'])) {
                $existe = User::where('email', $p['email'])
                    ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                    ->exists();
                if ($existe) $erros["pessoas.{$i}.email"] = ['Este email já está em uso.'];
            }

            if (!empty($p['cpf'])) {
                $cpfLimpo = Cpf::normalize($p['cpf']);
                $existe = User::where('cpf', $cpfLimpo)
                    ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                    ->exists();
                if ($existe) $erros["pessoas.{$i}.cpf"] = ['Este CPF já está cadastrado.'];
            }
        }

        if (!empty($erros)) {
            throw ValidationException::withMessages($erros);
        }

        return $data;
    }

    private function garantirUnicoResponsavel(array $pessoas): void
    {
        $marcados = collect($pessoas)->filter(fn($p) => !empty($p['is_responsavel']))->count();
        abort_if($marcados !== 1, 422, 'Exatamente uma pessoa precisa ser marcada como responsável.');
    }

    private function pessoasDisponiveis(): array
    {
        return User::query()
            ->whereNull('familia_id')
            ->where('is_superadmin', false)
            ->orderBy('name')
            ->get(['id', 'name', 'telefone', 'tipo', 'email', 'data_nascimento', 'sexo', 'estado_civil', 'cpf', 'batizado_aguas'])
            ->map(fn($u) => [
                'id'              => $u->id,
                'name'            => $u->name,
                'telefone'        => $u->telefone,
                'email'           => $u->email,
                'data_nascimento' => optional($u->data_nascimento)->format('Y-m-d'),
                'sexo'            => $u->sexo,
                'estado_civil'    => $u->estado_civil,
                'cpf'             => $u->cpf,
                'tipo'            => $u->tipo,
                'batizado_aguas'  => $u->batizado_aguas,
            ])
            ->all();
    }
}
