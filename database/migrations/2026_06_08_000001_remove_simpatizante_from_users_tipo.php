<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('tipo', 'simpatizante')->update(['tipo' => 'visitante']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('tipo', ['membro', 'visitante'])->default('membro')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('tipo', ['membro', 'visitante', 'simpatizante'])->default('membro')->change();
        });
    }
};
