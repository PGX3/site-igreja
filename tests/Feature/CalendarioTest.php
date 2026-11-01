<?php

namespace Tests\Feature;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Evento;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_abre_com_horario_de_evento_com_acento(): void
    {
        // "19h às 22h" cortado em 5 bytes partia o "à" ao meio e o JSON quebrava (500)
        Evento::create([
            'nome' => 'Marcha pra Jesus',
            'data_evento' => '2026-11-14',
            'horario' => '19h às 22h',
            'ativo' => true,
        ]);

        $pastor = User::create([
            'name' => 'Pastor',
            'email' => 'pastor@teste.com',
            'password' => 'segredo123',
            'role_id' => Role::where('name', 'pastor')->value('id'),
        ]);

        $this->actingAs($pastor)
            ->get('/admin/calendario?mes=2026-11', [
                'X-Inertia' => 'true',
                'X-Inertia-Version' => app(HandleInertiaRequests::class)->version(request()),
            ])
            ->assertOk()
            ->assertJsonPath('props.eventos.0.hora', '19h à');
    }
}
