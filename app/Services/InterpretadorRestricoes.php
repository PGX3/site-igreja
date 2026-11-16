<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Lê o texto livre da coluna "Não pode" do planejador e descobre de quais membros
 * ele fala. Com DeepSeek configurado entende apelidos e frases ("o Jô viaja").
 * Sem ele (ou se falhar), cai para a comparação simples de nomes.
 */
class InterpretadorRestricoes
{
    public function __construct(private DeepSeek $ia) {}

    /**
     * @param  array<int, array{data: string, vinculo: ?string, texto: string}>  $linhas  índice da linha => dados
     * @param  array<int, string>  $membros  user_id => nome
     * @return array{nao_podem: array<int, int[]>, ia: bool}
     */
    public function interpretar(array $linhas, array $membros): array
    {
        $linhas = array_filter($linhas, fn ($l) => trim($l['texto'] ?? '') !== '');

        if (! $linhas || ! $membros) {
            return ['nao_podem' => [], 'ia' => false];
        }

        $pelaIa = $this->pelaIa($linhas, $membros);

        return $pelaIa !== null
            ? ['nao_podem' => $pelaIa, 'ia' => true]
            : ['nao_podem' => $this->pelosNomes($linhas, $membros), 'ia' => false];
    }

    private function pelaIa(array $linhas, array $membros): ?array
    {
        $sistema = <<<'TXT'
        Você ajuda a montar escalas de voluntários de uma igreja. Para cada data, o líder
        anotou em texto livre quem NÃO pode servir. Identifique quais membros da lista o
        texto menciona como indisponíveis naquela data. Considere apelidos e primeiros nomes.
        Se o texto disser que a pessoa PODE, ou não falar de nenhum membro da lista, não
        inclua ninguém. Na dúvida entre dois membros de mesmo nome, inclua os dois.
        Responda somente em JSON, no formato:
        {"linhas": [{"linha": 0, "nao_podem": [12, 34]}]}
        TXT;

        $usuario = json_encode([
            'membros' => collect($membros)->map(fn ($nome, $id) => ['id' => $id, 'nome' => $nome])->values(),
            'linhas' => collect($linhas)->map(fn ($l, $i) => [
                'linha' => $i,
                'data' => $l['data'],
                'ocasiao' => $l['vinculo'] ?? null,
                'texto' => Str::limit(trim($l['texto']), 300, ''),
            ])->values(),
        ], JSON_UNESCAPED_UNICODE);

        $resposta = $this->ia->json($sistema, $usuario);

        if (! is_array($resposta['linhas'] ?? null)) {
            return null;
        }

        $naoPodem = [];
        foreach ($resposta['linhas'] as $item) {
            $i = $item['linha'] ?? null;
            if (! is_int($i) || ! isset($linhas[$i]) || ! is_array($item['nao_podem'] ?? null)) {
                continue;
            }

            // Só aceita ids que existem no grupo: a IA não inventa gente.
            $ids = array_values(array_unique(array_filter(
                array_map('intval', $item['nao_podem']),
                fn ($id) => isset($membros[$id]),
            )));

            if ($ids) {
                $naoPodem[$i] = $ids;
            }
        }

        return $naoPodem;
    }

    private function pelosNomes(array $linhas, array $membros): array
    {
        $naoPodem = [];

        foreach ($linhas as $i => $linha) {
            $texto = $this->normalizar($linha['texto']);
            $palavras = preg_split('/[^a-z0-9]+/', $texto, -1, PREG_SPLIT_NO_EMPTY);

            $ids = [];
            foreach ($membros as $id => $nome) {
                $nome = $this->normalizar($nome);
                $primeiro = strtok($nome, ' ');

                if ($nome !== '' && (str_contains($texto, $nome) || in_array($primeiro, $palavras, true))) {
                    $ids[] = (int) $id;
                }
            }

            if ($ids) {
                $naoPodem[$i] = $ids;
            }
        }

        return $naoPodem;
    }

    private function normalizar(string $texto): string
    {
        return trim(Str::lower(Str::ascii($texto)));
    }
}
