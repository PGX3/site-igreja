<?php

namespace Database\Seeders;

use App\Models\Culto;
use App\Models\Texto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
  
        DB::table('users')->insertOrIgnore([
            'name'              => 'Admin',
            'email'             => 'admin@igreja.com',
            'password'          => Hash::make('senha123'),
            'email_verified_at' => now(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);


        $cultos = [
            ['nome' => 'Escola Bíblica',     'dia_semana' => 'Domingo', 'horario' => '08h30', 'ativo' => true],
            ['nome' => 'Culto de Celebração', 'dia_semana' => 'Domingo', 'horario' => '10h00', 'ativo' => true],
            ['nome' => 'Culto de Oração',     'dia_semana' => 'Quarta',  'horario' => '19h30', 'ativo' => true],
            ['nome' => 'Grupos de Jovens',    'dia_semana' => 'Sexta',   'horario' => '19h30', 'ativo' => true],
        ];
        foreach ($cultos as $c) Culto::firstOrCreate(['nome' => $c['nome']], $c);

        // Textos editáveis
        $textos = [
            ['chave' => 'hero_titulo',    'label' => 'Hero — Título',    'conteudo' => 'Somos Igreja.'],
            ['chave' => 'hero_subtitulo', 'label' => 'Hero — Subtítulo', 'conteudo' => 'Charqueadas · Rio Grande do Sul · Brasil'],
            ['chave' => 'sobre_titulo',   'label' => 'Sobre — Título',   'conteudo' => 'Uma comunidade que sabe o que é.'],
            ['chave' => 'sobre_texto',    'label' => 'Sobre — Texto',    'conteudo' => 'Não somos "igreja de algo". Não somos "igreja para isso". Somos Igreja. Ponto. Uma comunidade enraizada em Charqueadas, definida pela cruz, movida por propósito — e fechada com um ponto que é só nosso.'],
            ['chave' => 'cta_titulo',     'label' => 'CTA — Título',     'conteudo' => 'Você é bem-vindo.'],
            ['chave' => 'endereco',       'label' => 'Endereço',         'conteudo' => 'Rua Exemplo, 123 — Charqueadas, RS'],
        ];
        foreach ($textos as $t) Texto::firstOrCreate(['chave' => $t['chave']], $t);
    }
}