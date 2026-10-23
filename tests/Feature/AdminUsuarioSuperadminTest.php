<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** O pastor não pode alterar a conta oculta do superadmin pelo painel. */
class AdminUsuarioSuperadminTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $nome, string $role = 'membro', bool $superadmin = false): User
    {
        return User::create([
            'name' => $nome,
            'email' => str()->slug($nome).'@exemplo.com',
            'password' => Hash::make('senha-original'),
            'tipo' => 'membro',
            'role_id' => Role::where('name', $role)->value('id'),
            'is_superadmin' => $superadmin,
        ]);
    }

    private function dados(): array
    {
        return [
            'name' => 'Novo Nome',
            'email' => 'novo@exemplo.com',
            'password' => 'senha-nova',
            'role_id' => Role::where('name', 'membro')->value('id'),
        ];
    }

    public function test_pastor_nao_altera_superadmin(): void
    {
        $pastor = $this->usuario('Pastor', 'pastor');
        $superadmin = $this->usuario('Super', 'pastor', superadmin: true);
        $antes = $superadmin->only('name', 'email', 'password', 'role_id');

        $this->actingAs($pastor)
            ->put("/admin/usuarios/{$superadmin->id}", $this->dados())
            ->assertForbidden();

        $this->assertSame($antes, $superadmin->fresh()->only('name', 'email', 'password', 'role_id'));
    }

    public function test_pastor_altera_usuario_comum(): void
    {
        $pastor = $this->usuario('Pastor', 'pastor');
        $membro = $this->usuario('Membro');

        $this->actingAs($pastor)
            ->put("/admin/usuarios/{$membro->id}", $this->dados())
            ->assertRedirect(route('admin.usuarios.index'));

        $membro->refresh();
        $this->assertSame('novo@exemplo.com', $membro->email);
        $this->assertTrue(Hash::check('senha-nova', $membro->password));
    }
}
