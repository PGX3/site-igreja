<?php

use App\Models\PregacaoCorte;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Cortes (Shorts) passam a ficar dentro da pregação. Os Shorts que o importador
 * antigo cadastrou como pregação viram cortes e somem da lista de pregações.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pregacao_cortes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pregacao_id')->nullable()->constrained('pregacoes')->nullOnDelete();
            $table->string('youtube_id', 20)->unique();
            $table->string('titulo', 200);
            $table->dateTime('publicado_em');
            $table->boolean('fixado')->default(false); // pastor escolheu a pregação à mão
            $table->boolean('ativo')->default(true);   // aparece no site
            $table->timestamps();

            $table->index(['pregacao_id', 'publicado_em']);
        });

        // Shorts cadastrados como pregação (só os importados: sem roteiro e sem pregador).
        $shorts = DB::table('pregacoes')
            ->where(fn ($q) => $q->whereRaw('LOWER(titulo) LIKE ?', ['%#shorts%'])->orWhere('youtube_url', 'like', '%/shorts/%'))
            ->whereNull('pregador_id')
            ->where(fn ($q) => $q->whereNull('roteiro')->orWhere('roteiro', ''))
            ->get();

        foreach ($shorts as $p) {
            preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/|live\/))([\w-]{11})/i', (string) $p->youtube_url, $m);
            if (! empty($m[1]) && ! DB::table('pregacao_cortes')->where('youtube_id', $m[1])->exists()) {
                $titulo = trim(preg_replace('/(\s*#[\p{L}\p{N}_]+)+\s*$/u', '', $p->titulo)) ?: $p->titulo;
                DB::table('pregacao_cortes')->insert([
                    'youtube_id' => $m[1],
                    'titulo' => Str::limit($titulo, 200, ''),
                    'publicado_em' => Carbon::parse($p->data_pregacao)->setTime(12, 0),
                    'fixado' => false,
                    'ativo' => (bool) $p->ativo,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            DB::table('pregacoes')->where('id', $p->id)->delete();
        }

        // Liga cada corte à pregação dele (a próxima importação refaz com as pregações que faltarem).
        if ($shorts->isNotEmpty()) {
            PregacaoCorte::revincular();
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pregacao_cortes');
    }
};
