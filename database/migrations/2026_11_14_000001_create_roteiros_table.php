<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Quem pode escrever roteiros de pregação (além do pastor, que sempre pode).
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_pregador')->default(false)->after('role_id');
        });

        // Roteiro (rascunho) da pregação: o pregador escreve no site e lê no iPad do púlpito.
        Schema::create('roteiros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('titulo', 150);
            $table->date('data');
            $table->string('versiculo', 120)->nullable();
            $table->longText('conteudo')->nullable();
            // Tamanho base da letra no iPad (px); destaques de tamanho no texto são relativos (em).
            $table->unsignedTinyInteger('tamanho_fonte')->default(26);
            $table->timestamps();

            $table->index('data');
        });

        // iPads do púlpito: leem os roteiros pela API com um token próprio (só o hash fica aqui).
        Schema::create('pulpito_dispositivos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 60);
            $table->string('token_hash', 64)->unique();
            $table->timestamp('ultimo_uso_em')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pulpito_dispositivos');
        Schema::dropIfExists('roteiros');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_pregador');
        });
    }
};
