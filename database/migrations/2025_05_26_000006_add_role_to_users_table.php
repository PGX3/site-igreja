<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->default(3)->constrained('roles');
            $table->foreignId('grupo_id')->nullable()->constrained('grupos');
            $table->string('telefone')->nullable();
            $table->json('disponibilidade')->nullable(); // dias/horários disponíveis
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropConstrainedForeignId('grupo_id');
            $table->dropColumn(['telefone', 'disponibilidade']);
        });
    }
};
