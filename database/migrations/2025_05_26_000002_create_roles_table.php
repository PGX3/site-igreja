<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Inserir roles padrão
        DB::table('roles')->insert([
            ['name' => 'pastor', 'display_name' => 'Pastor', 'created_at' => now()],
            ['name' => 'lider', 'display_name' => 'Líder', 'created_at' => now()],
            ['name' => 'membro', 'display_name' => 'Membro', 'created_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('roles');
    }
};
