<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 150);
            $table->string('slug')->unique();
            $table->text('descricao')->nullable();
            $table->string('capa_path')->nullable();
            $table->string('share_token', 40)->nullable()->unique();
            $table->boolean('ativo')->default(true);
            $table->integer('ordem')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
