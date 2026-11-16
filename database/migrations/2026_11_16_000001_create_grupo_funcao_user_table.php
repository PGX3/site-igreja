<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupo_funcao_user', function (Blueprint $table) {
            $table->foreignId('grupo_funcao_id')->constrained('grupo_funcoes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['grupo_funcao_id', 'user_id']);
            $table->timestamps();
        });

        $this->preencherPeloHistorico();
    }

    public function down(): void
    {
        Schema::dropIfExists('grupo_funcao_user');
    }

    /**
     * Ponto de partida: quem já foi escalado numa função do grupo (e ainda é membro)
     * começa marcado nela. O líder ajusta depois em Editar grupo.
     */
    private function preencherPeloHistorico(): void
    {
        $funcoes = DB::table('grupo_funcoes')->get(['id', 'grupo_id', 'nome'])->groupBy('grupo_id');
        $membros = DB::table('grupo_user')->get(['grupo_id', 'user_id'])
            ->groupBy('grupo_id')
            ->map(fn ($linhas) => $linhas->pluck('user_id')->flip());

        $escalados = DB::table('escala_membros')
            ->join('escalas', 'escalas.id', '=', 'escala_membros.escala_id')
            ->whereNotNull('escala_membros.funcao')
            ->select('escalas.grupo_id', 'escala_membros.user_id', 'escala_membros.funcao')
            ->distinct()
            ->get();

        $agora = now();
        $linhas = [];

        foreach ($escalados as $e) {
            if (! isset($membros[$e->grupo_id][$e->user_id])) {
                continue;
            }

            // "Camera, Instagram" conta para as duas funções
            $nomes = array_map('trim', explode(',', $e->funcao));
            $nomes[] = $e->funcao;

            foreach ($funcoes[$e->grupo_id] ?? [] as $f) {
                if (in_array($f->nome, $nomes, true)) {
                    $linhas[$f->id.'|'.$e->user_id] = [
                        'grupo_funcao_id' => $f->id,
                        'user_id' => $e->user_id,
                        'created_at' => $agora,
                        'updated_at' => $agora,
                    ];
                }
            }
        }

        foreach (array_chunk(array_values($linhas), 200) as $lote) {
            DB::table('grupo_funcao_user')->insert($lote);
        }
    }
};
