<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aula_anexos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aula_id')->constrained('aulas')->cascadeOnDelete();
            $table->enum('tipo', ['arquivo', 'link']);
            $table->string('titulo')->nullable();
            $table->string('arquivo_path')->nullable();
            $table->string('arquivo_nome')->nullable();
            $table->string('url')->nullable();
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aula_anexos');
    }
};
