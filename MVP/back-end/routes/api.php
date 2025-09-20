<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContatoOngController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\EnderecoController;
use App\Http\Controllers\OngController;
use App\Http\Controllers\ParceiroController;

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('jwt.auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
    Route::get('/me', [AuthController::class, 'me'])->name('me'); // GET, não POST
});

Route::apiResource('usuarios', UsuarioController::class);

Route::prefix('enderecos')->group(function () {
    Route::get('/', [EnderecoController::class, 'index']);          // Listar todos
    Route::post('/', [EnderecoController::class, 'store']);         // Criar novo
    Route::get('/{id}', [EnderecoController::class, 'show']);       // Buscar por id
    Route::put('/{id}', [EnderecoController::class, 'update']);     // Atualizar
    Route::delete('/{id}', [EnderecoController::class, 'destroy']); // Soft delete
    Route::post('/{id}/restore', [EnderecoController::class, 'restore']); // Restaurar
    Route::delete('/{id}/force', [EnderecoController::class, 'forceDelete']); // Delete definitivo
});

Route::apiResource('ongs', OngController::class);

Route::apiResource('parceiros', ParceiroController::class);

Route::apiResource ('contato-ongs',ContatoOngController::class);

Route::apiResource('documentos', DocumentoController::class);





