<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ContatoController;
use App\Http\Controllers\CadastroController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CultoController;
use App\Http\Controllers\Admin\EventoController;
use App\Http\Controllers\Admin\TextoController;
use App\Http\Controllers\Admin\SugestaoController;
use App\Http\Controllers\Admin\PedidoOracaoController;
use App\Http\Controllers\Admin\GrupoController;
use App\Http\Controllers\Admin\EscalaController;
use App\Http\Controllers\Admin\EscalaMembroController;
use App\Http\Controllers\Admin\AniversarioController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MembroController;
use App\Http\Controllers\Admin\VisitanteController;
use App\Http\Controllers\Admin\FamiliaController;
use App\Http\Controllers\Admin\MinhaSenhaController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/cultos/{culto}', [HomeController::class, 'showCulto'])->name('cultos.show');
Route::get('/eventos/{evento}', [HomeController::class, 'showEvento'])->name('eventos.show');

Route::post('/sugestao', [ContatoController::class, 'sugestao'])->name('sugestao.store');
Route::post('/pedido-oracao', [ContatoController::class, 'pedidoOracao'])->name('pedido-oracao.store');

Route::get('/cadastro', [CadastroController::class, 'create'])->name('cadastro.create');
Route::post('/cadastro', [CadastroController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('cadastro.store');
Route::get('/cadastro/obrigado', [CadastroController::class, 'obrigado'])->name('cadastro.obrigado');

Route::get('/login', fn() => inertia('Auth/Login'))->name('login');
Route::post('/login', [App\Http\Controllers\Auth\AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [App\Http\Controllers\Auth\AuthController::class, 'logout'])->name('logout');

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Aniversários (pastor + líder)
    Route::middleware('role:pastor,lider')->group(function () {
        Route::get('aniversarios', [AniversarioController::class, 'index'])->name('aniversarios.index');
    });

    // Todas as roles: ver suas próprias escalas e confirmar/recusar
    Route::get('minhas-escalas', [EscalaMembroController::class, 'index'])->name('minhas-escalas.index');
    Route::get('minha-senha', [MinhaSenhaController::class, 'edit'])->name('minha-senha.edit');
    Route::put('minha-senha', [MinhaSenhaController::class, 'update'])->name('minha-senha.update');
    Route::patch('escala-membros/{escalaMembro}/confirmar', [EscalaMembroController::class, 'confirmar'])->name('escala-membros.confirmar');
    Route::patch('escala-membros/{escalaMembro}/recusar', [EscalaMembroController::class, 'recusar'])->name('escala-membros.recusar');

    // Pastor + Líder: gerenciar escalas (líder restrito ao seu grupo via controller)
    Route::middleware('role:pastor,lider')->group(function () {
        Route::resource('escalas', EscalaController::class);

        Route::resource('membros', MembroController::class);
        Route::resource('visitantes', VisitanteController::class);
        Route::post('visitantes/{visitante}/promover', [VisitanteController::class, 'promoverParaMembro'])
            ->name('visitantes.promover');
        Route::resource('familias', FamiliaController::class);
    });

    // Apenas Pastor: grupos, cultos, textos, sugestões, pedidos, usuários
    Route::middleware('role:pastor')->group(function () {
        Route::resource('grupos', GrupoController::class);
        Route::resource('usuarios', UserController::class)->except(['show']);
        Route::put('usuarios/{usuario}/senha', [UserController::class, 'alterarSenha'])->name('usuarios.senha');
        Route::resource('cultos', CultoController::class);
        Route::resource('eventos', EventoController::class);
        Route::resource('textos', TextoController::class);

        Route::get('sugestoes', [SugestaoController::class, 'index'])->name('sugestoes.index');
        Route::patch('sugestoes/{sugestao}/lida', [SugestaoController::class, 'marcarLida'])->name('sugestoes.lida');
        Route::delete('sugestoes/{sugestao}', [SugestaoController::class, 'destroy'])->name('sugestoes.destroy');

        Route::get('pedidos-oracao', [PedidoOracaoController::class, 'index'])->name('pedidos-oracao.index');
        Route::patch('pedidos-oracao/{pedido}/lido', [PedidoOracaoController::class, 'marcarLido'])->name('pedidos-oracao.lido');
        Route::delete('pedidos-oracao/{pedido}', [PedidoOracaoController::class, 'destroy'])->name('pedidos-oracao.destroy');
    });
});
