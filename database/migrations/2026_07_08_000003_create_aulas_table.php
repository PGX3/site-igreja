<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aulas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modulo_id')->constrained('curso_modulos')->cascadeOnDelete();
            $table->string('titulo', 150);
            $table->text('conteudo')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('share_token', 40)->nullable()->unique();
            $table->integer('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aulas');
    }
};
