<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNotNull('cpf')
            ->orderBy('id')
            ->get(['id', 'cpf'])
            ->each(function ($u) {
                $limpo = preg_replace('/\D/', '', (string) $u->cpf);
                DB::table('users')->where('id', $u->id)->update(['cpf' => $limpo === '' ? null : $limpo]);
            });

        $duplicados = DB::table('users')
            ->select('cpf', DB::raw('COUNT(*) as total'))
            ->whereNotNull('cpf')
            ->groupBy('cpf')
            ->having('total', '>', 1)
            ->pluck('cpf')
            ->all();

        if (count($duplicados) > 0) {
            throw new \RuntimeException(
                'CPFs duplicados encontrados na tabela users: ' . implode(', ', $duplicados) .
                '. Resolva manualmente antes de rodar essa migration.'
            );
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('cpf');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['cpf']);
        });
    }
};
