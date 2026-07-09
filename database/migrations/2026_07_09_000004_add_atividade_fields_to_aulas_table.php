<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aulas', function (Blueprint $table) {
            $table->date('data_entrega')->nullable()->after('youtube_url');
            $table->unsignedSmallInteger('pontos')->nullable()->after('data_entrega');
        });
    }

    public function down(): void
    {
        Schema::table('aulas', function (Blueprint $table) {
            $table->dropColumn(['data_entrega', 'pontos']);
        });
    }
};
