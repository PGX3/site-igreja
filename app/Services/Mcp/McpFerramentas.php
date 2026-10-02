<?php

namespace App\Services\Mcp;

use App\Models\Culto;
use App\Models\Evento;
use App\Models\Igreja;
use App\Models\Pregacao;
use App\Models\Texto;
use App\Services\CultoProximaDataResolver;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Catálogo de ferramentas MCP de leitura sobre o conteúdo público do site.
 *
 * As descrições e os schemas daqui são o contrato com o modelo: é o que ele lê
 * para decidir se e quando chamar cada ferramenta. Trate-os como parte da
 * interface, não como comentário.
 */
class McpFerramentas
{
    public function __construct(private CultoProximaDataResolver $proximaData) {}

    /**
     * Definições no formato esperado por `tools/list`.
     *
     * @return array<int, array<string, mixed>>
     */
    public function definicoes(): array
    {
        $limiteMax = (int) config('mcp.limite_maximo');

        return [
            [
                'name' => 'info_igreja',
                'title' => 'Informações da igreja',
                'description' => 'Retorna nome, endereço, cidade, telefone, e-mail e site da Igreja em Charqueadas, '
                    .'junto com os textos institucionais publicados no site. '
                    .'Use quando perguntarem onde a igreja fica, como entrar em contato ou o que a igreja crê.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => new \stdClass,
                    'additionalProperties' => false,
                ],
                'annotations' => ['readOnlyHint' => true],
            ],
            [
                'name' => 'listar_cultos',
                'title' => 'Cultos semanais',
                'description' => 'Lista os cultos semanais ativos com dia da semana, horário e a data da próxima '
                    .'ocorrência. Use para perguntas do tipo "que horas é o culto", "quando tem culto" ou '
                    .'"tem culto no domingo".',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => new \stdClass,
                    'additionalProperties' => false,
                ],
                'annotations' => ['readOnlyHint' => true],
            ],
            [
                'name' => 'listar_eventos',
                'title' => 'Próximos eventos',
                'description' => 'Lista os próximos eventos da igreja (data, horário, local e descrição), do mais '
                    .'próximo para o mais distante. Eventos já ocorridos não aparecem. Use para perguntas sobre '
                    .'programação, agenda ou eventos especiais.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'limite' => [
                            'type' => 'integer',
                            'description' => 'Quantidade máxima de eventos a retornar.',
                            'minimum' => 1,
                            'maximum' => $limiteMax,
                        ],
                    ],
                    'additionalProperties' => false,
                ],
                'annotations' => ['readOnlyHint' => true],
            ],
            [
                'name' => 'buscar_pregacoes',
                'title' => 'Buscar pregações',
                'description' => 'Busca pregações publicadas por título, pregador ou versículo, da mais recente para '
                    .'a mais antiga. Retorna um resumo de cada uma com o id. Sem termo de busca, devolve as mais '
                    .'recentes. Para o conteúdo completo de uma pregação, chame depois ler_pregacao com o id.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'termo' => [
                            'type' => 'string',
                            'description' => 'Texto a buscar no título, no nome do pregador ou no versículo.',
                            'maxLength' => 120,
                        ],
                        'limite' => [
                            'type' => 'integer',
                            'description' => 'Quantidade máxima de pregações a retornar.',
                            'minimum' => 1,
                            'maximum' => $limiteMax,
                        ],
                    ],
                    'additionalProperties' => false,
                ],
                'annotations' => ['readOnlyHint' => true],
            ],
            [
                'name' => 'ler_pregacao',
                'title' => 'Ler pregação',
                'description' => 'Retorna o conteúdo completo de uma pregação pelo id, incluindo descrição, '
                    .'versículo e link do vídeo. Obtenha o id primeiro com buscar_pregacoes.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => [
                            'type' => 'integer',
                            'description' => 'Id da pregação, obtido em buscar_pregacoes.',
                            'minimum' => 1,
                        ],
                    ],
                    'required' => ['id'],
                    'additionalProperties' => false,
                ],
                'annotations' => ['readOnlyHint' => true],
            ],
        ];
    }

    public function existe(string $nome): bool
    {
        return in_array($nome, array_column($this->definicoes(), 'name'), true);
    }

    /**
     * Executa a ferramenta e devolve o texto que vai para o modelo.
     *
     * @param  array<string, mixed>  $argumentos
     *
     * @throws ValidationException argumentos inválidos
     * @throws McpFerramentaErro falha esperada de execução
     */
    public function executar(string $nome, array $argumentos): string
    {
        $dados = match ($nome) {
            'info_igreja' => $this->infoIgreja(),
            'listar_cultos' => $this->listarCultos(),
            'listar_eventos' => $this->listarEventos($argumentos),
            'buscar_pregacoes' => $this->buscarPregacoes($argumentos),
            'ler_pregacao' => $this->lerPregacao($argumentos),
            default => throw new McpFerramentaErro("Ferramenta desconhecida: {$nome}."),
        };

        return (string) json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    // ─────────────────────────────── Ferramentas ───────────────────────────────

    /** @return array<string, mixed> */
    private function infoIgreja(): array
    {
        $igreja = Igreja::atual();

        // Só campos de contato público: o CNPJ nunca sai daqui.
        return [
            'nome' => $igreja->nome,
            'endereco' => $igreja->endereco,
            'cidade' => $igreja->cidade,
            'telefone' => $igreja->telefone,
            'email' => $igreja->email,
            'site' => $igreja->site ?: url('/'),
            // Objeto vazio (não lista) quando não há textos, para o formato não variar.
            'textos_do_site' => Texto::orderBy('chave')->get()
                ->mapWithKeys(fn (Texto $t) => [$t->chave => self::textoSimples($t->conteudo)])
                ->filter()
                ->all() ?: new \stdClass,
        ];
    }

    /** @return array<string, mixed> */
    private function listarCultos(): array
    {
        $cultos = Culto::query()
            ->where('ativo', true)
            ->orderBy('id')
            ->get()
            ->map(fn (Culto $c) => [
                'nome' => $c->nome,
                'dia_semana' => $c->dia_semana,
                'horario' => self::horario($c->horario),
                'proxima_data' => $this->proximaData->resolve($c)->toDateString(),
                'descricao' => self::textoSimples($c->descricao),
                'url' => url("/cultos/{$c->id}"),
            ])
            ->values()
            ->all();

        return [
            'hoje' => Carbon::today()->toDateString(),
            'total' => count($cultos),
            'cultos' => $cultos,
        ];
    }

    /**
     * @param  array<string, mixed>  $argumentos
     * @return array<string, mixed>
     */
    private function listarEventos(array $argumentos): array
    {
        $dados = $this->validar($argumentos, [
            'limite' => ['sometimes', 'integer', 'min:1', 'max:'.config('mcp.limite_maximo')],
        ]);

        $limite = (int) ($dados['limite'] ?? config('mcp.limite_padrao'));

        $eventos = Evento::query()
            ->where('ativo', true)
            ->whereDate('data_evento', '>=', Carbon::today())
            ->orderBy('data_evento')
            ->limit($limite)
            ->get()
            ->map(fn (Evento $e) => [
                'nome' => $e->nome,
                'data_evento' => $e->data_evento->toDateString(),
                'horario' => self::horario($e->horario),
                'local' => $e->local,
                'descricao' => self::textoSimples($e->descricao),
                'url' => url("/eventos/{$e->id}"),
            ])
            ->values()
            ->all();

        return [
            'hoje' => Carbon::today()->toDateString(),
            'total' => count($eventos),
            'eventos' => $eventos,
        ];
    }

    /**
     * @param  array<string, mixed>  $argumentos
     * @return array<string, mixed>
     */
    private function buscarPregacoes(array $argumentos): array
    {
        $dados = $this->validar($argumentos, [
            'termo' => ['sometimes', 'string', 'max:120'],
            'limite' => ['sometimes', 'integer', 'min:1', 'max:'.config('mcp.limite_maximo')],
        ]);

        $termo = trim((string) ($dados['termo'] ?? ''));
        $limite = (int) ($dados['limite'] ?? config('mcp.limite_padrao'));

        $pregacoes = Pregacao::query()
            ->where('ativo', true)
            ->when($termo !== '', function ($query) use ($termo) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $termo).'%';

                $query->where(function ($q) use ($like) {
                    $q->where('titulo', 'like', $like)
                        ->orWhere('pregador', 'like', $like)
                        ->orWhere('versiculo', 'like', $like);
                });
            })
            ->orderByDesc('data_pregacao')
            ->limit($limite)
            ->get()
            ->map(fn (Pregacao $p) => [
                'id' => $p->id,
                'titulo' => $p->titulo,
                'pregador' => $p->pregador,
                'versiculo' => $p->versiculo,
                'data_pregacao' => $p->data_pregacao->toDateString(),
                'url' => url("/pregacoes/{$p->id}"),
            ])
            ->values()
            ->all();

        return [
            'termo' => $termo !== '' ? $termo : null,
            'total' => count($pregacoes),
            'pregacoes' => $pregacoes,
        ];
    }

    /**
     * @param  array<string, mixed>  $argumentos
     * @return array<string, mixed>
     */
    private function lerPregacao(array $argumentos): array
    {
        $dados = $this->validar($argumentos, [
            'id' => ['required', 'integer', 'min:1'],
        ]);

        $pregacao = Pregacao::query()
            ->where('ativo', true)
            ->find((int) $dados['id']);

        if (! $pregacao) {
            throw new McpFerramentaErro(
                'Pregação não encontrada ou não publicada. Use buscar_pregacoes para obter ids válidos.'
            );
        }

        return [
            'id' => $pregacao->id,
            'titulo' => $pregacao->titulo,
            'pregador' => $pregacao->pregador,
            'versiculo' => $pregacao->versiculo,
            'data_pregacao' => $pregacao->data_pregacao->toDateString(),
            'youtube_url' => $pregacao->youtube_url,
            'descricao' => self::textoSimples($pregacao->descricao),
            'url' => url("/pregacoes/{$pregacao->id}"),
        ];
    }

    // ───────────────────────────────── Apoio ─────────────────────────────────

    /**
     * Mensagens explícitas: o projeto não publica os arquivos de tradução, então
     * sem isto o erro chegaria ao modelo como a chave crua ("validation.max.numeric").
     */
    private const MENSAGENS = [
        'required' => 'O argumento ":attribute" é obrigatório.',
        'integer' => 'O argumento ":attribute" deve ser um número inteiro.',
        'string' => 'O argumento ":attribute" deve ser um texto.',
        'min' => 'O argumento ":attribute" deve ser no mínimo :min.',
        'max' => 'O argumento ":attribute" deve ser no máximo :max.',
    ];

    /**
     * @param  array<string, mixed>  $argumentos
     * @param  array<string, mixed>  $regras
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    private function validar(array $argumentos, array $regras): array
    {
        return Validator::make($argumentos, $regras, self::MENSAGENS)->validate();
    }

    private static function horario(?string $valor): ?string
    {
        return $valor ? substr($valor, 0, 5) : null;
    }

    /**
     * Converte o HTML do editor em texto corrido legível pelo modelo.
     */
    private static function textoSimples(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        // Quebras de bloco viram espaço para não colar palavras ao remover as tags.
        $comEspacos = preg_replace('/<(br|\/p|\/li|\/h[1-6]|\/blockquote)[^>]*>/i', ' ', $html);

        $texto = html_entity_decode(strip_tags((string) $comEspacos), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $texto = trim((string) preg_replace('/\s+/u', ' ', $texto));

        return $texto !== '' ? $texto : null;
    }
}
