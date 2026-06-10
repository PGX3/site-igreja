<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('familias', function (Blueprint $table) {
            $table->dropColumn('nome');
        });
    }

    public function down(): void
    {
        Schema::table('familias', function (Blueprint $table) {
            $table->string('nome', 100)->nullable()->after('id');
        });
    }
};
