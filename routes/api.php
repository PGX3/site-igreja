<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CadastroController;
use App\Http\Controllers\Api\V1\ContatoController;
use App\Http\Controllers\Api\V1\CultoController;
use App\Http\Controllers\Api\V1\DeviceTokenController;
use App\Http\Controllers\Api\V1\EscalaController;
use App\Http\Controllers\Api\V1\EventoController;
use App\Http\Controllers\Api\V1\GrupoController;
use App\Http\Controllers\Api\V1\MembroController;
use App\Http\Controllers\Api\V1\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('force.json')->group(function () {
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

        // Escalas
        Route::get('escalas', [EscalaController::class, 'index'])->name('api.v1.escalas.index');
        Route::post('escalas', [EscalaController::class, 'store'])->name('api.v1.escalas.store');
        Route::get('escalas/{escala}', [EscalaController::class, 'show'])->name('api.v1.escalas.show');
        Route::patch('escalas/{escala}', [EscalaController::class, 'update'])->name('api.v1.escalas.update');
        Route::delete('escalas/{escala}', [EscalaController::class, 'destroy'])->name('api.v1.escalas.destroy');
        Route::post('escalas/{escala}/membros', [EscalaController::class, 'addMembro'])->name('api.v1.escalas.membros.add');
        Route::delete('escalas/{escala}/membros/{escalaMembro}', [EscalaController::class, 'removeMembro'])->name('api.v1.escalas.membros.remove');
        Route::patch('escalas/membros/{escalaMembro}/confirmar', [EscalaController::class, 'confirmar'])->name('api.v1.escalas.confirmar');
        Route::patch('escalas/membros/{escalaMembro}/recusar', [EscalaController::class, 'recusar'])->name('api.v1.escalas.recusar');

        // Cultos e eventos
        Route::get('cultos', [CultoController::class, 'index'])->name('api.v1.cultos.index');
        Route::get('cultos/{culto}', [CultoController::class, 'show'])->name('api.v1.cultos.show');
        Route::get('eventos', [EventoController::class, 'index'])->name('api.v1.eventos.index');
        Route::get('eventos/{evento}', [EventoController::class, 'show'])->name('api.v1.eventos.show');

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
