<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos_oracao', function (Blueprint $table) {
            $table->string('status')->default('novo')->after('anonimo');
            $table->boolean('compartilhar')->default(false)->after('status');
        });

        // Migra os dados existentes: lido=true (ja orado) vira concluido, lido=false vira novo.
        DB::table('pedidos_oracao')->where('lido', true)->update(['status' => 'concluido']);
        DB::table('pedidos_oracao')->where('lido', false)->update(['status' => 'novo']);

        Schema::table('pedidos_oracao', function (Blueprint $table) {
            $table->dropColumn('lido');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos_oracao', function (Blueprint $table) {
            $table->boolean('lido')->default(false)->after('anonimo');
        });

        DB::table('pedidos_oracao')->where('status', 'concluido')->update(['lido' => true]);

        Schema::table('pedidos_oracao', function (Blueprint $table) {
            $table->dropColumn(['status', 'compartilhar']);
        });
    }
};
