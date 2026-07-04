<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sugestoes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('email')->nullable();
            $table->text('mensagem');
            $table->boolean('lida')->default(false);
            $table->timestamps();
        });

        Schema::create('pedidos_oracao', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->text('pedido');
            $table->boolean('anonimo')->default(false);
            $table->boolean('lido')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sugestoes');
        Schema::dropIfExists('pedidos_oracao');
    }
};
