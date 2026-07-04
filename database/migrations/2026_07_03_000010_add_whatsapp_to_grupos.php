<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            $table->string('whatsapp_apikey')->nullable()->after('tem_musicas');
            $table->string('whatsapp_phone')->nullable()->after('whatsapp_apikey');
        });
    }

    public function down(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_apikey', 'whatsapp_phone']);
        });
    }
};
