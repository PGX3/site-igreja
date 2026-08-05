<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('igreja', function (Blueprint $table) {
            $table->string('youtube_channel_id', 40)->nullable()->after('site');
        });
    }

    public function down(): void
    {
        Schema::table('igreja', function (Blueprint $table) {
            $table->dropColumn('youtube_channel_id');
        });
    }
};
