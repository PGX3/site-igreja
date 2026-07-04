<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $pastor = Role::where('name', 'pastor')->first();
        $lider = Role::where('name', 'lider')->first();
        $membro = Role::where('name', 'membro')->first();

        if (! $pastor || ! $lider || ! $membro) {
            $this->command->error('Roles não encontradas! Execute: php artisan migrate');

            return;
        }

        // ── PASTOR ──────────────────────────────────────────────────
        $pastorUser = User::updateOrCreate(
            ['email' => 'pastor@igreja.com'],
            [
                'name' => 'Pastor João',
                'password' => Hash::make('12345678'),
                'role_id' => $pastor->id,
                'telefone' => '(51) 99900-0001',
            ]
        );
    }
}
