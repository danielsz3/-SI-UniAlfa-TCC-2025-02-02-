<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContatoOngController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\EnderecoController;
use App\Http\Controllers\LaresTemporarioController;
use App\Http\Controllers\OngController;
use App\Http\Controllers\ParceiroController;
use App\Http\Controllers\TransacaoController;

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('jwt.auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::apiResource('usuarios', UsuarioController::class);
    Route::apiResource('enderecos', EnderecoController::class);
    Route::apiResource('ongs', OngController::class);
    Route::apiResource('parceiros', ParceiroController::class);
    Route::apiResource('lares-temporarios', LaresTemporarioController::class);
    Route::apiResource('contato-ongs', ContatoOngController::class);
    Route::apiResource('documentos', DocumentoController::class);
    Route::apiResource('transacoes', TransacaoController::class);
    Route::apiResource('lares-temporarios', LaresTemporarioController::class);

    Route::post('usuarios/{id}/restore', [UsuarioController::class, 'restore'])->name('usuarios.restore');
    Route::post('enderecos/{id}/restore', [EnderecoController::class, 'restore'])->name('enderecos.restore');
    Route::post('ongs/{id}/restore', [OngController::class, 'restore'])->name('ongs.restore');
    Route::post('parceiros/{id}/restore', [ParceiroController::class, 'restore'])->name('parceiros.restore');
    Route::post('lares-temporarios/{id}/restore', [LaresTemporarioController::class, 'restore'])->name('lares-temporarios.restore');
    Route::post('contato-ongs/{id}/restore', [ContatoOngController::class, 'restore'])->name('contato-ongs.restore');
    Route::post('documentos/{id}/restore', [DocumentoController::class, 'restore'])->name('documentos.restore');
    Route::post('transacoes/{id}/restore', [TransacaoController::class, 'restore'])->name('transacoes.restore');
    Route::post('lares-temporarios/{id}/restore', [LaresTemporarioController::class, 'restore'])->name('lares-temporarios.restore');

});


Route::get('usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
Route::get('usuarios/{id}', [UsuarioController::class, 'show'])->name('usuarios.show');


Route::get('enderecos', [EnderecoController::class, 'index'])->name('enderecos.index');
Route::get('enderecos/{id}', [EnderecoController::class, 'show'])->name('enderecos.show');

Route::get('ongs', [OngController::class, 'index'])->name('ongs.index');
Route::get('ongs/{id}', [OngController::class, 'show'])->name('ongs.show');

Route::get('parceiros', [ParceiroController::class, 'index'])->name('parceiros.index');
Route::get('parceiros/{id}', [ParceiroController::class, 'show'])->name('parceiros.show');

Route::get('lares-temporarios', [LaresTemporarioController::class, 'index'])->name('lares-temporarios.index');
Route::get('lares-temporarios/{id}', [LaresTemporarioController::class, 'show'])->name('lares-temporarios.show');

Route::get('contato-ongs', [ContatoOngController::class, 'index'])->name('contato-ongs.index');
Route::get('contato-ongs/{id}', [ContatoOngController::class, 'show'])->name('contato-ongs.show');

Route::get('documentos', [DocumentoController::class, 'index'])->name('documentos.index');
Route::get('documentos/{id}', [DocumentoController::class, 'show'])->name('documentos.show');

Route::get('transacoes', [TransacaoController::class, 'index'])->name('transacoes.index');
Route::get('transacoes/{id}', [TransacaoController::class, 'show'])->name('transacoes.show');

Route::get('lares-temporarios', [LaresTemporarioController::class, 'index'])->name('lares-temporarios.index');
Route::get('lares-temporarios/{id}', [LaresTemporarioController::class, 'show'])->name('lares-temporarios.show');