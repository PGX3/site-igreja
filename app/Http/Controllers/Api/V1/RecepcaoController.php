<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Igreja;
use App\Models\RecepcaoDispositivo;
use App\Models\User;
use App\Notifications\NovoVisitante;
use App\Services\ContatoService;
use App\Services\RegistroVisitante;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

/**
 * API do iPad da recepção (app "Recepção"). Autenticado pelo token do
 * aparelho (middleware `recepcao.token`), sem login de usuário.
 */
class RecepcaoController extends Controller
{
    public function __construct(
        private RegistroVisitante $registro,
        private ContatoService $contato,
    ) {}

    /** Dados que o app baixa ao abrir (e usa para saber se está online). */
    public function config(Request $request): JsonResponse
    {
        $dispositivo = $this->dispositivo($request);

        return response()->json([
            'dispositivo' => $dispositivo->nome,
            'igreja' => Igreja::query()->value('nome') ?: config('app.name'),
            'como_conheceu_opcoes' => User::COMO_CONHECEU_OPCOES,
            'tem_pin' => $dispositivo->temPin(),
        ]);
    }

    /** Libera o modo diácono: confere o PIN e devolve a lista de membros ("convidado por"). */
    public function diacono(Request $request): JsonResponse
    {
        $request->validate(['pin' => 'required|string|max:12']);

        if (! $this->dispositivo($request)->pinConfere($request->input('pin'))) {
            return response()->json(['message' => 'PIN incorreto.'], 403);
        }

        $membros = User::membros()
            ->where('is_superadmin', false)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $u) => ['id' => $u->id, 'name' => $u->name])
            ->all();

        return response()->json(['membros' => $membros]);
    }

    public function storeVisitante(Request $request): JsonResponse
    {
        $dispositivo = $this->dispositivo($request);

        // O app reenvia a fila quando a internet volta: o mesmo envio (uuid)
        // devolve a resposta guardada em vez de cadastrar de novo.
        $chaveEnvio = $this->chaveEnvio($request, $dispositivo);
        if ($chaveEnvio && ($anterior = Cache::get($chaveEnvio))) {
            return response()->json($anterior);
        }

        $data = $request->validate([
            'uuid' => 'nullable|string|max:64',
            'name' => 'required|string|min:2|max:100',
            'telefone' => 'required|string|min:8|max:20',
            'data_nascimento' => 'required|date|before:today|after:1900-01-01',
            'como_conheceu' => ['required', Rule::in(User::COMO_CONHECEU_OPCOES)],
            'como_conheceu_detalhe' => 'nullable|string|max:120',
            'autoriza_contato' => 'required|boolean',
            'pedido_oracao' => 'nullable|string|max:2000',
            'email' => 'nullable|email|max:191',
            // Modo diácono
            'pin' => 'nullable|string|max:12',
            'convidado_por_id' => ['nullable', Rule::exists('users', 'id')->where('tipo', 'membro')],
            'observacao' => 'nullable|string|max:2000',
        ], [
            'name.required' => 'Informe o nome.',
            'telefone.required' => 'Informe o WhatsApp.',
            'data_nascimento.required' => 'Informe a data de nascimento.',
            'data_nascimento.before' => 'A data de nascimento deve ser anterior a hoje.',
            'como_conheceu.in' => 'Escolha uma das opções da lista.',
        ]);

        $modoDiacono = filled($data['convidado_por_id'] ?? null) || filled($data['observacao'] ?? null);
        if ($modoDiacono && ! $dispositivo->pinConfere($data['pin'] ?? null)) {
            return response()->json(['message' => 'PIN do diácono inválido.'], 403);
        }

        $nome = trim(preg_replace('/\s+/', ' ', $data['name']));
        $comoConheceu = $data['como_conheceu'] === 'Outro' && filled($data['como_conheceu_detalhe'] ?? null)
            ? trim($data['como_conheceu_detalhe'])
            : $data['como_conheceu'];
        $autoriza = (bool) $data['autoriza_contato'];
        $observacao = filled($data['observacao'] ?? null)
            ? '['.Carbon::today()->format('d/m/Y').' · recepção] '.trim($data['observacao'])
            : null;

        $existente = $this->registro->porTelefone($data['telefone']);

        $visitante = DB::transaction(function () use ($existente, $data, $nome, $comoConheceu, $autoriza, $observacao) {
            if ($existente) {
                // Já cadastrado: completa o que está em branco e respeita a
                // última resposta sobre contato. Quem já é membro não é tocado.
                if ($existente->tipo === 'visitante') {
                    $existente->fill(array_filter([
                        'email' => $existente->email ?: ($data['email'] ?? null),
                        'data_nascimento' => $existente->data_nascimento ?: $data['data_nascimento'],
                        'como_conheceu' => $existente->como_conheceu ?: $comoConheceu,
                        'convidado_por_id' => $existente->convidado_por_id ?: ($data['convidado_por_id'] ?? null),
                    ]));
                    if ($observacao) {
                        $existente->observacoes_pastorais = trim(($existente->observacoes_pastorais ?? '')."\n".$observacao);
                    }
                    $existente->forceFill(['autoriza_contato' => $autoriza, 'autoriza_contato_em' => now()]);
                    $existente->save();
                }

                return $existente;
            }

            $novo = User::create([
                'name' => $nome,
                'email' => $data['email'] ?? null,
                'telefone' => $data['telefone'],
                'data_nascimento' => $data['data_nascimento'],
                'como_conheceu' => $comoConheceu,
                'convidado_por_id' => $data['convidado_por_id'] ?? null,
                'observacoes_pastorais' => $observacao,
                'primeira_visita' => Carbon::today()->toDateString(),
                'tipo' => 'visitante',
            ]);
            $novo->forceFill(['autoriza_contato' => $autoriza, 'autoriza_contato_em' => now()])->save();

            return $novo;
        });

        if (filled($data['pedido_oracao'] ?? null)) {
            $this->contato->criarPedidoOracao([
                'nome' => $nome,
                'pedido' => trim($data['pedido_oracao']),
            ]);
        }

        $dispositivo->increment('cadastros');

        if (! $existente) {
            $this->avisarPastores($visitante, $dispositivo);
        }

        $resposta = [
            'ok' => true,
            'retorno' => (bool) $existente,
            'primeiro_nome' => strtok($nome, ' '),
        ];

        if ($chaveEnvio) {
            Cache::put($chaveEnvio, $resposta, now()->addDays(3));
        }

        return response()->json($resposta, $existente ? 200 : 201);
    }

    /** Aba "Oração" do iPad: vai direto para Pedidos de Oração. */
    public function storeOracao(Request $request): JsonResponse
    {
        $dispositivo = $this->dispositivo($request);
        $chaveEnvio = $this->chaveEnvio($request, $dispositivo);
        if ($chaveEnvio && ($anterior = Cache::get($chaveEnvio))) {
            return response()->json($anterior);
        }

        $data = $request->validate([
            'uuid' => 'nullable|string|max:64',
            'pedido' => 'required|string|min:3|max:2000',
            'nome' => 'nullable|string|max:100',
            'anonimo' => 'boolean',
            'compartilhar' => 'boolean',
        ], [
            'pedido.required' => 'Escreva o pedido.',
        ]);

        $anonimo = (bool) ($data['anonimo'] ?? false) || blank($data['nome'] ?? null);
        $this->contato->criarPedidoOracao([
            'nome' => trim((string) ($data['nome'] ?? '')),
            'pedido' => trim($data['pedido']),
            'anonimo' => $anonimo,
            'compartilhar' => (bool) ($data['compartilhar'] ?? false),
        ]);

        return $this->responderEnvio($chaveEnvio, ['ok' => true]);
    }

    /** Aba "Sugestão" do iPad: cai em Sugestões no painel. */
    public function storeSugestao(Request $request): JsonResponse
    {
        $dispositivo = $this->dispositivo($request);
        $chaveEnvio = $this->chaveEnvio($request, $dispositivo);
        if ($chaveEnvio && ($anterior = Cache::get($chaveEnvio))) {
            return response()->json($anterior);
        }

        $data = $request->validate([
            'uuid' => 'nullable|string|max:64',
            'mensagem' => 'required|string|min:3|max:2000',
            'nome' => 'nullable|string|max:100',
        ], [
            'mensagem.required' => 'Escreva a sugestão.',
        ]);

        $this->contato->criarSugestao([
            'nome' => filled($data['nome'] ?? null) ? trim($data['nome']) : 'Anônimo (recepção)',
            'mensagem' => trim($data['mensagem']),
        ]);

        return $this->responderEnvio($chaveEnvio, ['ok' => true]);
    }

    /** Chave para não repetir um envio que o app reenviou da fila (mesmo uuid). */
    private function chaveEnvio(Request $request, RecepcaoDispositivo $dispositivo): ?string
    {
        $uuid = (string) $request->input('uuid', '');

        return $uuid !== '' ? 'recepcao:envio:'.$dispositivo->id.':'.$uuid : null;
    }

    private function responderEnvio(?string $chaveEnvio, array $resposta): JsonResponse
    {
        if ($chaveEnvio) {
            Cache::put($chaveEnvio, $resposta, now()->addDays(3));
        }

        return response()->json($resposta, 201);
    }

    private function avisarPastores(User $visitante, RecepcaoDispositivo $dispositivo): void
    {
        try {
            $pastores = User::whereHas('role', fn ($q) => $q->where('name', 'pastor'))->get();
            Notification::send($pastores, new NovoVisitante($visitante, $dispositivo->nome));
        } catch (\Throwable $e) {
            // Aviso é bônus: o cadastro já está salvo.
            Log::warning("Aviso de novo visitante falhou: {$e->getMessage()}");
        }
    }

    private function dispositivo(Request $request): RecepcaoDispositivo
    {
        return $request->attributes->get('recepcao');
    }
}
