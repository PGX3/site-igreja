<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('escalas', function (Blueprint $table) {
            $table->foreignId('culto_id')->nullable()->after('grupo_id')
                ->constrained('cultos')->nullOnDelete();
            $table->foreignId('evento_id')->nullable()->after('culto_id')
                ->constrained('eventos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('escalas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('culto_id');
            $table->dropConstrainedForeignId('evento_id');
        });
    }
};
