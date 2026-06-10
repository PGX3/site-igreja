<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('familias', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->string('endereco');
            $table->string('cidade', 80);
            $table->string('uf', 2);
            $table->string('cep', 10);
            $table->string('telefone_principal', 20)->nullable();
            $table->foreignId('responsavel_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('familia_id')
                ->nullable()
                ->after('is_superadmin')
                ->constrained('familias')
                ->nullOnDelete();
            $table->index('familia_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['familia_id']);
            $table->dropIndex(['familia_id']);
            $table->dropColumn('familia_id');
        });

        Schema::dropIfExists('familias');
    }
};
