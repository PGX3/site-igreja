<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Buscar o ID do papel de pastor
        $pastorRole = Role::where('name', 'pastor')->first();
        
        if (!$pastorRole) {
            $this->command->error('Role "pastor" não encontrada! Execute as migrations primeiro.');
            return;
        }
        
        // Criar pastor
        User::updateOrCreate(
            ['email' => 'pastor@igreja.com'],
            [
                'name' => 'Pastor João',
                'password' => Hash::make('12345678'),
                'role_id' => $pastorRole->id,
                'telefone' => '(11) 99999-9999'
            ]
        );
        
        $this->command->info('Pastor criado com sucesso!');
        $this->command->info('Email: pastor@igreja.com');
        $this->command->info('Senha: 12345678');
    }
}