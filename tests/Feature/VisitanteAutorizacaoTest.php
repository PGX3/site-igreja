<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Only the pastor may edit or promote a visitor, even when the request skips the UI. */
class VisitanteAutorizacaoTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $role): User
    {
        return User::create([
            'name' => ucfirst($role),
            'email' => $role.'@exemplo.com',
            'tipo' => 'membro',
            'role_id' => Role::where('name', $role)->value('id'),
        ]);
    }

    private function visitante(): User
    {
        return User::create([
            'name' => 'Joana da Silva',
            'email' => 'joana@exemplo.com',
            'telefone' => '(51) 99282-4071',
            'data_nascimento' => '1992-04-17',
            'tipo' => 'visitante',
        ]);
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return [
            'name' => 'Joana Alterada',
            'telefone' => '(51) 90000-0000',
            'data_nascimento' => '1990-01-01',
            'email' => 'alterado@exemplo.com',
            'cpf' => '529.982.247-25',
            'observacoes_pastorais' => 'Alterado pelo líder',
        ];
    }

    public function test_lider_nao_edita_visitante(): void
    {
        $visitante = $this->visitante();

        $this->actingAs($this->usuario('lider'))
            ->put("/admin/visitantes/{$visitante->id}", $this->payload())
            ->assertForbidden();

        $visitante->refresh();
        $this->assertSame('Joana da Silva', $visitante->name);
        $this->assertSame('joana@exemplo.com', $visitante->email);
        $this->assertSame('(51) 99282-4071', $visitante->telefone);
        $this->assertNull($visitante->cpf);
        $this->assertNull($visitante->observacoes_pastorais);
    }

    public function test_lider_nao_promove_visitante(): void
    {
        $visitante = $this->visitante();

        $this->actingAs($this->usuario('lider'))
            ->post("/admin/visitantes/{$visitante->id}/promover")
            ->assertForbidden();

        $this->assertSame('visitante', $visitante->refresh()->tipo);
    }

    public function test_pastor_edita_e_promove_visitante(): void
    {
        $visitante = $this->visitante();
        $pastor = $this->usuario('pastor');

        $this->actingAs($pastor)
            ->put("/admin/visitantes/{$visitante->id}", $this->payload())
            ->assertRedirect('/admin/visitantes');

        $this->assertSame('alterado@exemplo.com', $visitante->refresh()->email);

        $this->actingAs($pastor)
            ->post("/admin/visitantes/{$visitante->id}/promover")
            ->assertRedirect("/admin/membros/{$visitante->id}/edit");

        $this->assertSame('membro', $visitante->refresh()->tipo);
    }
}
