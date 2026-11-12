<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Aparelhos da recepção (iPad de cadastro de visitantes). O token só é
        // mostrado uma vez no painel; aqui fica apenas o hash.
        Schema::create('recepcao_dispositivos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 60);
            $table->string('token_hash', 64)->unique();
            $table->string('pin_hash')->nullable();
            $table->timestamp('ultimo_uso_em')->nullable();
            $table->unsignedInteger('cadastros')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // LGPD: o visitante autorizou a igreja a falar com ele pelo WhatsApp?
        // null = não foi perguntado (cadastros antigos). `_em` = quando respondeu.
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('autoriza_contato')->nullable()->after('primeira_visita');
            $table->timestamp('autoriza_contato_em')->nullable()->after('autoriza_contato');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['autoriza_contato', 'autoriza_contato_em']);
        });

        Schema::dropIfExists('recepcao_dispositivos');
    }
};
