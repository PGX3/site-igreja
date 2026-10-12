<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mudanças pontuais num culto fixo (ex.: "nesta semana o Culto Jovem será na sexta").
        Schema::create('culto_excecoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('culto_id')->constrained('cultos')->cascadeOnDelete();
            $table->date('data');                       // data original da ocorrência
            $table->date('nova_data')->nullable();      // remarcado para
            $table->string('novo_horario')->nullable(); // "20:00" ou "20h"
            $table->boolean('cancelado')->default(false);
            $table->string('observacao')->nullable();   // texto do ⚠️ na agenda
            $table->timestamps();

            $table->unique(['culto_id', 'data']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('culto_excecoes');
    }
};
