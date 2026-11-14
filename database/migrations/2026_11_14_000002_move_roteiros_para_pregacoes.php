<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * O roteiro passa a ser parte da Pregação: a mesma pregação nasce como rascunho
     * (roteiro do pregador, sem vídeo) e é publicada quando ganha o link do YouTube.
     */
    public function up(): void
    {
        Schema::table('pregacoes', function (Blueprint $table) {
            $table->string('youtube_url')->nullable()->change();
            $table->foreignId('pregador_id')->nullable()->after('pregador')->constrained('users')->nullOnDelete();
            $table->longText('roteiro')->nullable()->after('descricao');
            $table->unsignedTinyInteger('roteiro_fonte')->default(26)->after('roteiro');
        });

        // Roteiros já escritos viram pregações em rascunho.
        if (Schema::hasTable('roteiros')) {
            foreach (DB::table('roteiros')->orderBy('id')->get() as $r) {
                DB::table('pregacoes')->insert([
                    'titulo' => $r->titulo,
                    'pregador' => DB::table('users')->where('id', $r->user_id)->value('name'),
                    'pregador_id' => $r->user_id,
                    'versiculo' => $r->versiculo,
                    'youtube_url' => null,
                    'roteiro' => $r->conteudo,
                    'roteiro_fonte' => $r->tamanho_fonte,
                    'data_pregacao' => $r->data,
                    'ativo' => true,
                    'created_at' => $r->created_at,
                    'updated_at' => $r->updated_at,
                ]);
            }
            Schema::drop('roteiros');
        }
    }

    public function down(): void
    {
        Schema::create('roteiros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('titulo', 150);
            $table->date('data');
            $table->string('versiculo', 120)->nullable();
            $table->longText('conteudo')->nullable();
            $table->unsignedTinyInteger('tamanho_fonte')->default(26);
            $table->timestamps();
            $table->index('data');
        });

        DB::table('pregacoes')->whereNull('youtube_url')->delete();

        Schema::table('pregacoes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pregador_id');
            $table->dropColumn(['roteiro', 'roteiro_fonte']);
            $table->string('youtube_url')->nullable(false)->change();
        });
    }
};
