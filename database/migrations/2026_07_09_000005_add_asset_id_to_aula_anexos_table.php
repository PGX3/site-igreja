<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aula_anexos', function (Blueprint $table) {
            $table->foreignId('asset_id')->nullable()->after('aula_id')->constrained('assets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('aula_anexos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('asset_id');
        });
    }
};
