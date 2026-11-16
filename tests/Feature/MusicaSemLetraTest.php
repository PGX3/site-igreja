<?php

namespace Tests\Feature;

use App\Models\Musica;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MusicaSemLetraTest extends TestCase
{
    use RefreshDatabase;

    public function test_cadastra_musica_so_com_o_nome(): void
    {
        $pastor = User::create([
            'name' => 'Pastor',
            'email' => 'pastor'.uniqid().'@teste.com',
            'password' => 'segredo123',
            'role_id' => Role::where('name', 'pastor')->value('id'),
        ]);

        $this->actingAs($pastor)
            ->post('/admin/musicas', ['nome' => 'Oceanos', 'tom' => '', 'letra' => '', 'link' => ''])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $musica = Musica::sole();
        $this->assertSame('Oceanos', $musica->nome);
        $this->assertSame('', $musica->letra);
    }
}
