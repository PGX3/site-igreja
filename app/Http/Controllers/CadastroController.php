<?php

namespace App\Http\Controllers;

use App\Models\Familia;
use App\Models\User;
use App\Services\RecaptchaService;
use App\Support\Cpf;
use App\Support\Telefone;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CadastroController extends Controller
{
    public function __construct(
        private RecaptchaService $captcha,
    ) {}

    public function create()
    {
        return Inertia::render('Cadastro/Form');
    }

    public function store(Request $request)
    {
        if ($request->filled('_honey')) {
            return redirect()->route('cadastro.obrigado');
        }

        $request->merge(['cpf' => Cpf::normalize($request->input('cpf'))]);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:191|unique:users,email',
            'telefone' => 'required|string|max:20',
            'data_nascimento' => 'required|date|before:today',
            'sexo' => 'nullable|in:M,F',
            'estado_civil' => 'nullable|string|max:30',
            'cpf' => 'nullable|string|max:14|unique:users,cpf',
            'endereco' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:80',
            'uf' => 'nullable|string|size:2',
            'cep' => 'nullable|string|max:10',
            'como_conheceu' => 'nullable|string|max:255',
            'primeira_visita' => 'nullable|date|before_or_equal:today',
            'tipo' => 'required|in:membro,visitante',
            'batizado_aguas' => 'required|boolean',
        ]);

        DB::transaction(function () use ($data) {
            $familiaId = null;

            if (! empty($data['endereco']) || ! empty($data['cidade']) || ! empty($data['cep'])) {
                $familia = Familia::create([
                    'endereco' => $data['endereco'] ?? '',
                    'cidade' => $data['cidade'] ?? '',
                    'uf' => strtoupper($data['uf'] ?? ''),
                    'cep' => $data['cep'] ?? '',
                    'telefone_principal' => $data['telefone'] ?? null,
                ]);
                $familiaId = $familia->id;
            }

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'telefone' => $data['telefone'],
                'data_nascimento' => $data['data_nascimento'] ?? null,
                'sexo' => $data['sexo'] ?? null,
                'estado_civil' => $data['estado_civil'] ?? null,
                'cpf' => $data['cpf'] ?? null,
                'como_conheceu' => $data['como_conheceu'] ?? null,
                'primeira_visita' => $data['primeira_visita'] ?? null,
                'tipo' => $data['tipo'],
                'batizado_aguas' => $data['batizado_aguas'] ?? null,
                'familia_id' => $familiaId,
            ]);

            if ($familiaId) {
                Familia::where('id', $familiaId)->update(['responsavel_id' => $user->id]);
            }
        });

        return redirect()->route('cadastro.obrigado');
    }

    public function obrigado()
    {
        return Inertia::render('Cadastro/Obrigado');
    }

    /**
     * Versão curta do cadastro, pensada para quem acabou de visitar: só o
     * necessário para o follow-up pastoral. O restante dos dados é coletado
     * depois, no admin, via `visitantes.promover`.
     */
    public function createVisitante()
    {
        return Inertia::render('Cadastro/Visitante', [
            'comoConheceuOpcoes' => User::COMO_CONHECEU_OPCOES,
        ]);
    }

    public function storeVisitante(Request $request)
    {
        if ($request->filled('_honey')) {
            return redirect()->route('cadastro.visitante.obrigado');
        }

        if (! $this->captcha->verify($request->input('g-recaptcha-response'), $request->ip(), 'cadastro_visitante')) {
            return back()->withErrors([
                'captcha' => 'Não foi possível confirmar que você não é um robô. Tente novamente.',
            ])->withInput();
        }

        // Buscado antes da validação para que o próprio visitante, ao reenviar o
        // formulário, não bata no `unique` do e-mail dele mesmo.
        $existente = $this->usuarioPorTelefone($request->input('telefone'));

        $data = $request->validate([
            'name' => 'required|string|min:2|max:100',
            'telefone' => 'required|string|min:8|max:20',
            'data_nascimento' => 'required|date|before:today',
            'como_conheceu' => ['required', Rule::in(User::COMO_CONHECEU_OPCOES)],
            'como_conheceu_detalhe' => 'nullable|string|max:120',
            'email' => [
                'nullable', 'email', 'max:191',
                Rule::unique('users', 'email')->ignore($existente?->id),
            ],
        ], [
            'name.required' => 'Informe seu nome.',
            'name.min' => 'O nome deve ter ao menos 2 caracteres.',
            'telefone.required' => 'Informe um telefone para a gente falar com você.',
            'telefone.min' => 'Informe um telefone válido.',
            'data_nascimento.required' => 'Informe sua data de nascimento.',
            'data_nascimento.before' => 'A data de nascimento deve ser anterior a hoje.',
            'como_conheceu.required' => 'Conte como você nos conheceu.',
            'como_conheceu.in' => 'Escolha uma das opções da lista.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Esse e-mail já está cadastrado.',
        ]);

        $comoConheceu = $data['como_conheceu'] === 'Outro' && filled($data['como_conheceu_detalhe'] ?? null)
            ? trim($data['como_conheceu_detalhe'])
            : $data['como_conheceu'];

        if ($existente) {
            // Reenvio do mesmo número: completa só o que está em branco. O histórico
            // pastoral (primeira_visita, como_conheceu original, observações) fica
            // de pé, e quem já é membro não é tocado.
            if ($existente->tipo === 'visitante') {
                $existente->fill(array_filter([
                    'email' => $existente->email ?: ($data['email'] ?? null),
                    'data_nascimento' => $existente->data_nascimento ?: $data['data_nascimento'],
                    'como_conheceu' => $existente->como_conheceu ?: $comoConheceu,
                ]))->save();
            }

            return redirect()->route('cadastro.visitante.obrigado');
        }

        User::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'telefone' => $data['telefone'],
            'data_nascimento' => $data['data_nascimento'],
            'como_conheceu' => $comoConheceu,
            'primeira_visita' => Carbon::today()->toDateString(),
            'tipo' => 'visitante',
        ]);

        return redirect()->route('cadastro.visitante.obrigado');
    }

    public function obrigadoVisitante()
    {
        return Inertia::render('Cadastro/Obrigado', [
            'rotulo' => 'Recebido',
            'titulo' => ['Que bom ter você', 'por aqui.'],
            'mensagem' => 'Seu contato chegou para a equipe de acolhimento. Em breve alguém vai te chamar '
                .'só para dizer oi e saber se você precisa de alguma coisa.',
        ]);
    }

    /**
     * Procura um usuário pelo telefone comparando só os dígitos, já que a coluna
     * guarda o número formatado de jeitos diferentes. O `like` nos últimos 4
     * dígitos apenas reduz o conjunto, a igualdade é decidida em PHP.
     */
    private function usuarioPorTelefone(?string $telefone): ?User
    {
        $sufixo = Telefone::sufixo($telefone);

        if ($sufixo === null) {
            return null;
        }

        return User::whereNotNull('telefone')
            ->where('telefone', 'like', '%'.$sufixo.'%')
            ->get()
            ->first(fn (User $u) => Telefone::mesmoNumero($u->telefone, $telefone));
    }
}
