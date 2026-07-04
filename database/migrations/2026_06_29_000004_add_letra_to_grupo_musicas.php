<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grupo_musicas', function (Blueprint $table) {
            $table->text('letra')->nullable()->after('tom');
            $table->string('arquivo_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('grupo_musicas', function (Blueprint $table) {
            $table->dropColumn('letra');
            $table->string('arquivo_path')->nullable(false)->change();
        });
    }
};
