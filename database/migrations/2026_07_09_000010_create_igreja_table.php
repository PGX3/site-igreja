<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('igreja', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('cnpj', 20)->nullable();
            $table->string('endereco')->nullable();
            $table->string('cidade', 120)->nullable();
            $table->string('telefone', 40)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('site', 150)->nullable();
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('igreja');
    }
};
