<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pregacoes', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 150);
            $table->string('pregador', 120)->nullable();
            $table->string('versiculo', 120)->nullable();
            $table->string('youtube_url');
            $table->text('descricao')->nullable();
            $table->date('data_pregacao');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pregacoes');
    }
};
