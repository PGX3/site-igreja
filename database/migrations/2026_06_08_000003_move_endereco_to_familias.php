<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $usersComEndereco = DB::table('users')
            ->whereNull('familia_id')
            ->where(function ($q) {
                $q->whereNotNull('endereco')
                  ->orWhereNotNull('cidade')
                  ->orWhereNotNull('cep');
            })
            ->get();

        foreach ($usersComEndereco as $user) {
            $partes = preg_split('/\s+/', trim($user->name ?? ''), -1, PREG_SPLIT_NO_EMPTY);
            $sobrenome = count($partes) > 1 ? end($partes) : ($partes[0] ?? 'Sem Nome');
            $nomeFamilia = 'Família ' . $sobrenome;

            $familiaId = DB::table('familias')->insertGetId([
                'nome'               => mb_substr($nomeFamilia, 0, 100),
                'endereco'           => $user->endereco ?? '',
                'cidade'             => $user->cidade ?? '',
                'uf'                 => $user->uf ?? '',
                'cep'                => $user->cep ?? '',
                'telefone_principal' => $user->telefone,
                'responsavel_id'     => $user->id,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            DB::table('users')->where('id', $user->id)->update(['familia_id' => $familiaId]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['endereco', 'cidade', 'uf', 'cep']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('endereco')->nullable()->after('cpf');
            $table->string('cidade', 80)->nullable()->after('endereco');
            $table->string('uf', 2)->nullable()->after('cidade');
            $table->string('cep', 10)->nullable()->after('uf');
        });

        $users = DB::table('users')->whereNotNull('familia_id')->get();
        foreach ($users as $user) {
            $f = DB::table('familias')->where('id', $user->familia_id)->first();
            if ($f) {
                DB::table('users')->where('id', $user->id)->update([
                    'endereco' => $f->endereco,
                    'cidade'   => $f->cidade,
                    'uf'       => $f->uf,
                    'cep'      => $f->cep,
                ]);
            }
        }
    }
};
