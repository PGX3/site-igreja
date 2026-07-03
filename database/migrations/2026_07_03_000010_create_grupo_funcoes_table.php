<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupo_funcoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_id')->constrained('grupos')->cascadeOnDelete();
            $table->string('nome', 100);
            $table->timestamps();

            $table->unique(['grupo_id', 'nome']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupo_funcoes');
    }
};
