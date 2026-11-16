<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('musicas', function (Blueprint $table) {
            $table->string('artista', 150)->nullable()->after('nome');
            $table->unsignedSmallInteger('bpm')->nullable()->after('tom');
            $table->string('categoria', 20)->nullable()->after('bpm');
            // Versão do telão (SPresenter/Holyrics): seções na ordem cantada, [{tipo, texto}]
            $table->json('secoes_publico')->nullable()->after('letra');
        });
    }

    public function down(): void
    {
        Schema::table('musicas', function (Blueprint $table) {
            $table->dropColumn(['artista', 'bpm', 'categoria', 'secoes_publico']);
        });
    }
};
