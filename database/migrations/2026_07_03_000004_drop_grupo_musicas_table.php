<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('grupo_musicas');
    }

    public function down(): void
    {
        Schema::create('grupo_musicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_id')->constrained('grupos')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('nome', 200);
            $table->string('tom', 10)->nullable();
            $table->text('letra')->nullable();
            $table->string('arquivo_path')->nullable();
            $table->timestamps();
        });
    }
};
