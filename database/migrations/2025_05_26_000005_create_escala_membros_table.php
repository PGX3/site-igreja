
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('escala_membros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('escala_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('funcao')->nullable();
            $table->enum('status', ['pendente', 'confirmado', 'recusado', 'trocado'])->default('pendente');
            $table->text('observacao')->nullable();
            $table->timestamp('confirmado_em')->nullable();
            $table->timestamps();
            
            $table->unique(['escala_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('escala_membros');
    }
};