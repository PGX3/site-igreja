<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $pastor = Role::where('name', 'pastor')->first();
        $lider  = Role::where('name', 'lider')->first();
        $membro = Role::where('name', 'membro')->first();

        if (!$pastor || !$lider || !$membro) {
            $this->command->error('Roles não encontradas! Execute: php artisan migrate');
            return;
        }

        // ── PASTOR ──────────────────────────────────────────────────
        $pastorUser = User::updateOrCreate(
            ['email' => 'pastor@igreja.com'],
            [
                'name'     => 'Pastor João',
                'password' => Hash::make('12345678'),
                'role_id'  => $pastor->id,
                'telefone' => '(51) 99900-0001',
            ]
        );

        // ── LÍDERES (sem grupo ainda — grupos precisam do lider_id) ─
        $liderLouvor = User::updateOrCreate(
            ['email' => 'pedro.silva@igreja.com'],
            [
                'name'     => 'Pedro Silva',
                'password' => Hash::make('12345678'),
                'role_id'  => $lider->id,
                'telefone' => '(51) 99900-0002',
            ]
        );

        $liderRecepcao = User::updateOrCreate(
            ['email' => 'ana.oliveira@igreja.com'],
            [
                'name'     => 'Ana Oliveira',
                'password' => Hash::make('12345678'),
                'role_id'  => $lider->id,
                'telefone' => '(51) 99900-0003',
            ]
        );

        $liderSom = User::updateOrCreate(
            ['email' => 'carlos.santos@igreja.com'],
            [
                'name'     => 'Carlos Santos',
                'password' => Hash::make('12345678'),
                'role_id'  => $lider->id,
                'telefone' => '(51) 99900-0004',
            ]
        );

        // ── GRUPOS ──────────────────────────────────────────────────
        $grupoLouvor = DB::table('grupos')->updateOrInsert(
            ['nome' => 'Louvor'],
            [
                'descricao'  => 'Equipe de música e adoração',
                'lider_id'   => $liderLouvor->id,
                'created_by' => $pastorUser->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $grupoRecepcao = DB::table('grupos')->updateOrInsert(
            ['nome' => 'Recepção'],
            [
                'descricao'  => 'Equipe de boas-vindas e recepção',
                'lider_id'   => $liderRecepcao->id,
                'created_by' => $pastorUser->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $grupoSom = DB::table('grupos')->updateOrInsert(
            ['nome' => 'Som e Projeção'],
            [
                'descricao'  => 'Equipe de som, projeção e transmissão',
                'lider_id'   => $liderSom->id,
                'created_by' => $pastorUser->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $grupoKids = DB::table('grupos')->updateOrInsert(
            ['nome' => 'Kids'],
            [
                'descricao'  => 'Equipe do ministério infantil',
                'lider_id'   => null,
                'created_by' => $pastorUser->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Buscar IDs dos grupos criados
        $idLouvor    = DB::table('grupos')->where('nome', 'Louvor')->value('id');
        $idRecepcao  = DB::table('grupos')->where('nome', 'Recepção')->value('id');
        $idSom       = DB::table('grupos')->where('nome', 'Som e Projeção')->value('id');
        $idKids      = DB::table('grupos')->where('nome', 'Kids')->value('id');

        // Vincular líderes aos seus grupos
        $liderLouvor->update(['grupo_id' => $idLouvor]);
        $liderRecepcao->update(['grupo_id' => $idRecepcao]);
        $liderSom->update(['grupo_id' => $idSom]);

        // ── MEMBROS ─────────────────────────────────────────────────
        $membros = [
            // Louvor
            ['name' => 'Maria Costa',      'email' => 'maria.costa@igreja.com',      'grupo_id' => $idLouvor,   'telefone' => '(51) 99900-0005'],
            ['name' => 'João Ferreira',     'email' => 'joao.ferreira@igreja.com',    'grupo_id' => $idLouvor,   'telefone' => '(51) 99900-0006'],
            ['name' => 'Fernanda Lima',     'email' => 'fernanda.lima@igreja.com',    'grupo_id' => $idLouvor,   'telefone' => '(51) 99900-0007'],
            ['name' => 'Rafael Borges',     'email' => 'rafael.borges@igreja.com',    'grupo_id' => $idLouvor,   'telefone' => '(51) 99900-0008'],
            // Recepção
            ['name' => 'Lucas Rodrigues',   'email' => 'lucas.rodrigues@igreja.com',  'grupo_id' => $idRecepcao, 'telefone' => '(51) 99900-0009'],
            ['name' => 'Beatriz Alves',     'email' => 'beatriz.alves@igreja.com',    'grupo_id' => $idRecepcao, 'telefone' => '(51) 99900-0010'],
            ['name' => 'Thiago Mendes',     'email' => 'thiago.mendes@igreja.com',    'grupo_id' => $idRecepcao, 'telefone' => '(51) 99900-0011'],
            // Som e Projeção
            ['name' => 'Roberto Pereira',   'email' => 'roberto.pereira@igreja.com',  'grupo_id' => $idSom,      'telefone' => '(51) 99900-0012'],
            ['name' => 'Camila Souza',      'email' => 'camila.souza@igreja.com',     'grupo_id' => $idSom,      'telefone' => '(51) 99900-0013'],
            // Kids
            ['name' => 'Julia Castro',      'email' => 'julia.castro@igreja.com',     'grupo_id' => $idKids,     'telefone' => '(51) 99900-0014'],
            ['name' => 'André Martins',     'email' => 'andre.martins@igreja.com',    'grupo_id' => $idKids,     'telefone' => '(51) 99900-0015'],
            ['name' => 'Patrícia Nunes',    'email' => 'patricia.nunes@igreja.com',   'grupo_id' => $idKids,     'telefone' => '(51) 99900-0016'],
        ];

        foreach ($membros as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'password' => Hash::make('12345678'),
                    'role_id'  => $membro->id,
                    'grupo_id' => $data['grupo_id'],
                    'telefone' => $data['telefone'],
                ]
            );
        }

        $this->command->info('');
        $this->command->info('✓ Usuários e grupos criados com sucesso!');
        $this->command->info('');
        $this->command->info('  Senha de todos: 12345678');
        $this->command->info('');
        $this->command->info('  PASTOR');
        $this->command->info('    pastor@igreja.com');
        $this->command->info('');
        $this->command->info('  LÍDERES');
        $this->command->info('    pedro.silva@igreja.com    → Louvor');
        $this->command->info('    ana.oliveira@igreja.com   → Recepção');
        $this->command->info('    carlos.santos@igreja.com  → Som e Projeção');
        $this->command->info('');
        $this->command->info('  MEMBROS (4×Louvor, 3×Recepção, 2×Som, 3×Kids)');
        $this->command->info('    maria.costa@igreja.com');
        $this->command->info('    joao.ferreira@igreja.com');
        $this->command->info('    fernanda.lima@igreja.com');
        $this->command->info('    rafael.borges@igreja.com');
        $this->command->info('    lucas.rodrigues@igreja.com');
        $this->command->info('    beatriz.alves@igreja.com');
        $this->command->info('    thiago.mendes@igreja.com');
        $this->command->info('    roberto.pereira@igreja.com');
        $this->command->info('    camila.souza@igreja.com');
        $this->command->info('    julia.castro@igreja.com');
        $this->command->info('    andre.martins@igreja.com');
        $this->command->info('    patricia.nunes@igreja.com');
        $this->command->info('');
    }
}
