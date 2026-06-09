<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('tipo', ['membro', 'visitante', 'simpatizante'])
                ->default('membro')
                ->after('is_superadmin');
            $table->index('tipo');

            $table->enum('sexo', ['M', 'F'])->nullable()->after('data_nascimento');
            $table->string('estado_civil', 30)->nullable()->after('sexo');
            $table->string('cpf', 14)->nullable()->after('estado_civil');
            $table->string('endereco')->nullable()->after('cpf');
            $table->string('cidade', 80)->nullable()->after('endereco');
            $table->string('uf', 2)->nullable()->after('cidade');
            $table->string('cep', 10)->nullable()->after('uf');

            $table->string('como_conheceu')->nullable()->after('cep');
            $table->foreignId('convidado_por_id')
                ->nullable()
                ->after('como_conheceu')
                ->constrained('users')
                ->nullOnDelete();
            $table->date('primeira_visita')->nullable()->after('convidado_por_id');
            $table->text('observacoes_pastorais')->nullable()->after('primeira_visita');

            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
            $table->unsignedBigInteger('role_id')->nullable()->change();
        });

        DB::table('users')->whereNull('tipo')->update(['tipo' => 'membro']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['convidado_por_id']);
            $table->dropIndex(['tipo']);
            $table->dropColumn([
                'tipo', 'sexo', 'estado_civil', 'cpf',
                'endereco', 'cidade', 'uf', 'cep',
                'como_conheceu', 'convidado_por_id',
                'primeira_visita', 'observacoes_pastorais',
            ]);

            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
            $table->unsignedBigInteger('role_id')->nullable(false)->change();
        });
    }
};
