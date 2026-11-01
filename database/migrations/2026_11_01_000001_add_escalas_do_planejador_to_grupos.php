<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Eventos aparecem no planejador do grupo? (padrão: sim)
        Schema::table('grupos', function (Blueprint $table) {
            $table->boolean('escala_eventos')->default(true)->after('tem_musicas');
        });

        // Cultos que o grupo NÃO escala. Guardar a exceção faz um culto novo
        // aparecer em todos os grupos sem precisar marcar um por um.
        Schema::create('grupo_cultos_sem_escala', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('culto_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['grupo_id', 'culto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupo_cultos_sem_escala');

        Schema::table('grupos', function (Blueprint $table) {
            $table->dropColumn('escala_eventos');
        });
    }
};
