<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OngController;
use App\Http\Controllers\ParceiroController;
use App\Http\Controllers\ContatoOngController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\EnderecoController;


//Route::post('/register', [AuthController::class, 'register']); 
Route::post('/login', [AuthController::class, 'login']);
Route::apiResource('usuarios', UsuarioController::class)->only(['store']);
Route::apiResource('ongs', OngController::class)->only(['index', 'show']);
Route::apiResource('parceiros', ParceiroController::class)->only(['index', 'show']);
Route::apiResource('contato-ongs', ContatoOngController::class)->only(['index', 'show']);
Route::apiResource('documentos', DocumentoController::class)->only(['index', 'show']);
Route::get('documentos/{id}/download', [DocumentoController::class, 'download']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::middleware('role:user')->group(function () {
        Route::put('/usuarios/{id}', [UsuarioController::class, 'update']);
        Route::post('/enderecos', [EnderecoController::class, 'store']);
        Route::put('/enderecos/{id}', [EnderecoController::class, 'update']);
        Route::delete('/enderecos/{id}', [EnderecoController::class, 'destroy']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::apiResource('usuarios', UsuarioController::class);
        Route::apiResource('ongs', OngController::class)->except(['index', 'show']);

        Route::apiResource('parceiros', ParceiroController::class)->except(['index', 'show']);
        Route::get('parceiros-todos', [ParceiroController::class, 'indexWithTrashed']);
        Route::post('parceiros/{id}/restore', [ParceiroController::class, 'restore']);
        Route::post('parceiros/{id}/toggle-status', [ParceiroController::class, 'toggleStatus']);
        Route::delete('parceiros/{id}/force', [ParceiroController::class, 'forceDelete']);

        Route::apiResource('contato-ongs', ContatoOngController::class)->except(['index', 'show']);

        Route::apiResource('documentos', DocumentoController::class)->except(['index', 'show']);
        Route::apiResource('documentos', DocumentoController::class);
        Route::post('documentos/{id}/restore', [DocumentoController::class, 'restore']);

        Route::apiResource('enderecos', EnderecoController::class)->except(['store']);
    });
});
