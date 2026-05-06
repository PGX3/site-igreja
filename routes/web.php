<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ContatoController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CultoController;
use App\Http\Controllers\Admin\TextoController;
use App\Http\Controllers\Admin\SugestaoController;
use App\Http\Controllers\Admin\PedidoOracaoController;


Route::get('/', [HomeController::class, 'index'])->name('home');

Route::post('/sugestao', [ContatoController::class, 'sugestao'])->name('sugestao.store');
Route::post('/pedido-oracao', [ContatoController::class, 'pedidoOracao'])->name('pedido-oracao.store');


Route::get('/login', fn() => inertia('Auth/Login'))->name('login');
Route::post('/login', [App\Http\Controllers\Auth\AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [App\Http\Controllers\Auth\AuthController::class, 'logout'])->name('logout');


Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('cultos', CultoController::class);
    Route::resource('textos', TextoController::class);

    Route::get('sugestoes', [SugestaoController::class, 'index'])->name('sugestoes.index');
    Route::patch('sugestoes/{sugestao}/lida', [SugestaoController::class, 'marcarLida'])->name('sugestoes.lida');
    Route::delete('sugestoes/{sugestao}', [SugestaoController::class, 'destroy'])->name('sugestoes.destroy');

    Route::get('pedidos-oracao', [PedidoOracaoController::class, 'index'])->name('pedidos-oracao.index');
    Route::patch('pedidos-oracao/{pedido}/lido', [PedidoOracaoController::class, 'marcarLido'])->name('pedidos-oracao.lido');
    Route::delete('pedidos-oracao/{pedido}', [PedidoOracaoController::class, 'destroy'])->name('pedidos-oracao.destroy');
});
