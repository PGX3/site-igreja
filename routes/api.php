<?php

use App\Http\Controllers\Api\V1\AniversarioController;
use App\Http\Controllers\Api\V1\AprenderController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AutomacaoController;
use App\Http\Controllers\Api\V1\CadastroController;
use App\Http\Controllers\Api\V1\ContatoController;
use App\Http\Controllers\Api\V1\CultoController;
use App\Http\Controllers\Api\V1\DeviceTokenController;
use App\Http\Controllers\Api\V1\EscalaController;
use App\Http\Controllers\Api\V1\EscalaNotaController;
use App\Http\Controllers\Api\V1\EscalaSetlistController;
use App\Http\Controllers\Api\V1\EventoController;
use App\Http\Controllers\Api\V1\GrupoController;
use App\Http\Controllers\Api\V1\GrupoMuralController;
use App\Http\Controllers\Api\V1\MembroController;
use App\Http\Controllers\Api\V1\MusicaController;
use App\Http\Controllers\Api\V1\PedidoOracaoController;
use App\Http\Controllers\Api\V1\PregacaoController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Mcp\McpController;
use Illuminate\Support\Facades\Route;

// Servidor MCP para agentes de IA (somente leitura de conteúdo público).
// Desligado enquanto MCP_ENABLED não estiver true.
Route::post('mcp', [McpController::class, 'handle'])
    ->middleware(['force.json', 'mcp.token', 'throttle:mcp'])
    ->name('api.mcp');

Route::prefix('v1')->middleware('force.json')->group(function () {
    // Automações internas (token estático AUTOMACAO_TOKEN): agenda e aniversariantes no WhatsApp
    Route::prefix('automacao')->name('api.v1.automacao.')
        ->middleware(['automacao.token', 'throttle:api'])
        ->group(function () {
            Route::get('aniversariantes-semana', [AutomacaoController::class, 'aniversariantesSemana'])->name('aniversariantes-semana');
            Route::get('agenda-semana', [AutomacaoController::class, 'agendaSemana'])->name('agenda-semana');
            Route::get('cultos', [AutomacaoController::class, 'cultos'])->name('cultos');
            Route::get('culto-excecoes', [AutomacaoController::class, 'excecoes'])->name('culto-excecoes.index');
            Route::post('culto-excecoes', [AutomacaoController::class, 'salvarExcecao'])->name('culto-excecoes.store');
            Route::delete('culto-excecoes/{excecao}', [AutomacaoController::class, 'removerExcecao'])->name('culto-excecoes.destroy');
        });

    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('api.v1.auth.login');

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
        Route::post('auth/logout-all', [AuthController::class, 'logoutAll'])->name('api.v1.auth.logout-all');
        Route::get('auth/me', [AuthController::class, 'me'])->name('api.v1.auth.me');

        // Profile
        Route::get('profile', [ProfileController::class, 'show'])->name('api.v1.profile.show');
        Route::patch('profile', [ProfileController::class, 'update'])->name('api.v1.profile.update');
        Route::patch('profile/disponibilidade', [ProfileController::class, 'updateDisponibilidade'])->name('api.v1.profile.disponibilidade');
        Route::patch('profile/senha', [ProfileController::class, 'updatePassword'])->name('api.v1.profile.senha');

        // Cadastro de membros/visitantes pelo app (qualquer membro logado)
        Route::post('cadastro', [CadastroController::class, 'store'])
            ->middleware('throttle:cadastro')
            ->name('api.v1.cadastro.store');

        // Grupos e membros (selects do app)
        Route::get('grupos', [GrupoController::class, 'index'])->name('api.v1.grupos.index');
        Route::get('membros', [MembroController::class, 'index'])->name('api.v1.membros.index');

        // Mural do grupo (lê quem é do grupo, publica quem gerencia: autorização no controller)
        Route::get('grupos/{grupo}/avisos', [GrupoMuralController::class, 'index'])->name('api.v1.grupos.avisos.index');
        Route::post('grupos/{grupo}/avisos', [GrupoMuralController::class, 'store'])->name('api.v1.grupos.avisos.store');
        Route::delete('grupos/{grupo}/avisos/{aviso}', [GrupoMuralController::class, 'destroy'])->name('api.v1.grupos.avisos.destroy');

        // Escalas
        Route::get('escalas', [EscalaController::class, 'index'])->name('api.v1.escalas.index');
        Route::get('escalas/gerenciadas', [EscalaController::class, 'gerenciadas'])->name('api.v1.escalas.gerenciadas');
        Route::post('escalas', [EscalaController::class, 'store'])->name('api.v1.escalas.store');
        Route::get('escalas/{escala}', [EscalaController::class, 'show'])->name('api.v1.escalas.show');
        Route::patch('escalas/{escala}', [EscalaController::class, 'update'])->name('api.v1.escalas.update');
        Route::delete('escalas/{escala}', [EscalaController::class, 'destroy'])->name('api.v1.escalas.destroy');
        Route::post('escalas/{escala}/membros', [EscalaController::class, 'addMembro'])->name('api.v1.escalas.membros.add');
        Route::delete('escalas/{escala}/membros/{escalaMembro}', [EscalaController::class, 'removeMembro'])->name('api.v1.escalas.membros.remove');
        Route::patch('escalas/membros/{escalaMembro}/confirmar', [EscalaController::class, 'confirmar'])->name('api.v1.escalas.confirmar');
        Route::patch('escalas/membros/{escalaMembro}/recusar', [EscalaController::class, 'recusar'])->name('api.v1.escalas.recusar');

        // Setlist e notas da escala (quem gerencia o grupo: autorização no controller)
        Route::post('escalas/{escala}/setlist', [EscalaSetlistController::class, 'store'])->name('api.v1.escalas.setlist.store');
        Route::patch('escalas/{escala}/setlist/reorder', [EscalaSetlistController::class, 'reorder'])->name('api.v1.escalas.setlist.reorder');
        Route::patch('escalas/{escala}/setlist/{item}', [EscalaSetlistController::class, 'update'])->name('api.v1.escalas.setlist.update');
        Route::delete('escalas/{escala}/setlist/{item}', [EscalaSetlistController::class, 'destroy'])->name('api.v1.escalas.setlist.destroy');
        Route::post('escalas/{escala}/notas', [EscalaNotaController::class, 'store'])->name('api.v1.escalas.notas.store');
        Route::delete('escalas/{escala}/notas/{nota}', [EscalaNotaController::class, 'destroy'])->name('api.v1.escalas.notas.destroy');

        // Repertório (leitura para os músicos)
        Route::get('musicas', [MusicaController::class, 'index'])->name('api.v1.musicas.index');
        Route::get('musicas/{musica}', [MusicaController::class, 'show'])->name('api.v1.musicas.show');

        // Pregações publicadas
        Route::get('pregacoes', [PregacaoController::class, 'index'])->name('api.v1.pregacoes.index');
        Route::get('pregacoes/{pregacao}', [PregacaoController::class, 'show'])->name('api.v1.pregacoes.show');

        // Pedidos de oração recebidos (só pastor, mesma regra do painel)
        Route::middleware('role:pastor')->group(function () {
            Route::get('pedidos-oracao', [PedidoOracaoController::class, 'index'])->name('api.v1.pedidos-oracao.index');
            Route::patch('pedidos-oracao/{pedido}/status', [PedidoOracaoController::class, 'atualizarStatus'])->name('api.v1.pedidos-oracao.status');
        });

        // Cultos e eventos
        Route::get('cultos', [CultoController::class, 'index'])->name('api.v1.cultos.index');
        Route::get('cultos/{culto}', [CultoController::class, 'show'])->name('api.v1.cultos.show');
        Route::get('eventos', [EventoController::class, 'index'])->name('api.v1.eventos.index');
        Route::get('eventos/{evento}', [EventoController::class, 'show'])->name('api.v1.eventos.show');

        // Aniversariantes (pastor + líder, mesma regra do painel)
        Route::get('aniversarios', [AniversarioController::class, 'index'])
            ->middleware('role:pastor,lider')
            ->name('api.v1.aniversarios.index');

        // Escola bíblica (qualquer membro logado lê os cursos ativos)
        Route::get('aprender', [AprenderController::class, 'index'])->name('api.v1.aprender.index');
        Route::get('aprender/aulas/{aula}', [AprenderController::class, 'aula'])->name('api.v1.aprender.aula');
        Route::get('aprender/{curso}', [AprenderController::class, 'curso'])->name('api.v1.aprender.curso');

        // Contato (membro logado)
        Route::post('contato/sugestao', [ContatoController::class, 'sugestao'])
            ->middleware('throttle:contato')
            ->name('api.v1.contato.sugestao');
        Route::post('contato/pedido-oracao', [ContatoController::class, 'pedidoOracao'])
            ->middleware('throttle:contato-oracao')
            ->name('api.v1.contato.pedido-oracao');

        // Device tokens (FCM)
        Route::post('devices', [DeviceTokenController::class, 'store'])
            ->middleware('throttle:devices')
            ->name('api.v1.devices.store');
        Route::delete('devices/{token}', [DeviceTokenController::class, 'destroy'])
            ->middleware('throttle:devices')
            ->where('token', '.*')
            ->name('api.v1.devices.destroy');
    });
});
