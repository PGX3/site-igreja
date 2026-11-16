<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * Preenche as células vazias da grade do planejador de forma equilibrada.
 * É regra pura (sem IA): o mesmo mês sempre dá a mesma sugestão.
 *
 * Para cada célula vazia escolhe, entre quem faz a função e está livre no dia:
 *   1. quem não serviu no mesmo dia da semana na semana anterior/seguinte,
 *   2. quem ainda não está em outra ocasião do mesmo dia,
 *   3. quem tem menos escalas no mês,
 *   4. quem serviu menos nos meses anteriores,
 *   5. quem está há mais tempo sem servir.
 */
class SugestorEscala
{
    /**
     * @param  array<int, array{data: string, cells: array<string, int|string|null>}>  $linhas
     * @param  string[]  $funcoes  colunas do grupo
     * @param  int[]  $membros  ids dos membros do grupo
     * @param  array<string, int[]>  $aptos  função => quem faz (vazio = qualquer membro)
     * @param  array<int, string[]>  $historico  user_id => datas (Y-m-d) em que serviu antes do mês
     * @param  array<string, array<int, mixed>>  $ocupados  data => [user_id => outro grupo]
     * @param  array<int, int[]>  $naoPodem  índice da linha => ids indisponíveis
     * @return array{sugestoes: array<int, array{linha: int, funcao: string, user_id: int}>, sem_candidato: array<int, array{linha: int, funcao: string}>}
     */
    public function sugerir(
        array $linhas,
        array $funcoes,
        array $membros,
        array $aptos = [],
        array $historico = [],
        array $ocupados = [],
        array $naoPodem = [],
    ): array {
        $membros = array_map('intval', $membros);

        // Datas em que cada pessoa já serve: grade atual, outros grupos e meses anteriores.
        $datas = [];
        $cargaMes = array_fill_keys($membros, 0);

        foreach ($historico as $uid => $ds) {
            foreach ($ds as $d) {
                $datas[(int) $uid][$d] = true;
            }
        }
        foreach ($ocupados as $d => $pessoas) {
            foreach (array_keys($pessoas) as $uid) {
                $datas[(int) $uid][$d] = true;
                if (isset($cargaMes[(int) $uid])) {
                    $cargaMes[(int) $uid]++;
                }
            }
        }

        $naLinha = []; // índice => [user_id => true]
        $noDia = [];   // data => [user_id => nº de linhas]
        foreach ($linhas as $i => $linha) {
            $naLinha[$i] = [];
            foreach (array_unique(array_map('intval', array_filter($linha['cells'] ?? []))) as $uid) {
                $this->registrar($uid, $i, $linha['data'], $naLinha, $noDia, $datas, $cargaMes);
            }
        }

        $ordem = array_keys($linhas);
        usort($ordem, fn ($a, $b) => [$linhas[$a]['data'], $a] <=> [$linhas[$b]['data'], $b]);

        $sugestoes = [];
        $semCandidato = [];

        foreach ($ordem as $i) {
            $data = $linhas[$i]['data'];
            $vazias = array_values(array_filter($funcoes, fn ($f) => empty($linhas[$i]['cells'][$f])));

            // $naLinha por referência: quem acabou de entrar numa função sai das outras
            $livres = function (string $f) use (&$naLinha, $i, $data, $aptos, $membros, $ocupados, $naoPodem) {
                return array_values(array_filter(
                    ! empty($aptos[$f]) ? array_intersect($membros, array_map('intval', $aptos[$f])) : $membros,
                    fn ($uid) => ! isset($naLinha[$i][$uid])
                        && ! isset($ocupados[$data][$uid])
                        && ! in_array($uid, $naoPodem[$i] ?? [], false),
                ));
            };

            // Função com menos gente disponível escolhe primeiro
            usort($vazias, fn ($a, $b) => count($livres($a)) <=> count($livres($b)));

            foreach ($vazias as $funcao) {
                $candidatos = $livres($funcao);

                if (! $candidatos) {
                    $semCandidato[] = ['linha' => $i, 'funcao' => $funcao];

                    continue;
                }

                usort($candidatos, fn ($a, $b) => $this->peso($a, $data, $datas, $noDia, $cargaMes, $historico)
                    <=> $this->peso($b, $data, $datas, $noDia, $cargaMes, $historico));

                $uid = $candidatos[0];
                $this->registrar($uid, $i, $data, $naLinha, $noDia, $datas, $cargaMes);
                $sugestoes[] = ['linha' => $i, 'funcao' => $funcao, 'user_id' => $uid];
            }
        }

        return ['sugestoes' => $sugestoes, 'sem_candidato' => $semCandidato];
    }

    private function registrar(int $uid, int $linha, string $data, array &$naLinha, array &$noDia, array &$datas, array &$cargaMes): void
    {
        $naLinha[$linha][$uid] = true;
        $noDia[$data][$uid] = ($noDia[$data][$uid] ?? 0) + 1;
        $datas[$uid][$data] = true;
        $cargaMes[$uid] = ($cargaMes[$uid] ?? 0) + 1;
    }

    /** Menor é melhor. Comparado campo a campo, na ordem das regras da classe. */
    private function peso(int $uid, string $data, array $datas, array $noDia, array $cargaMes, array $historico): array
    {
        $dia = Carbon::parse($data);
        $seguido = isset($datas[$uid][$dia->copy()->subWeek()->toDateString()])
            || isset($datas[$uid][$dia->copy()->addWeek()->toDateString()]);

        $anteriores = $historico[$uid] ?? [];

        return [
            $seguido ? 1 : 0,
            isset($noDia[$data][$uid]) ? 1 : 0,
            $cargaMes[$uid] ?? 0,
            count($anteriores),
            $anteriores ? max($anteriores) : '',
            $uid,
        ];
    }
}
