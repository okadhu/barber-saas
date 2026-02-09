<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AgendamentoController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\ServicoController;
use App\Http\Controllers\Api\BarbeariaController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API Routes com autenticação Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Barbearias
    Route::apiResource('barbearias', BarbeariaController::class);
    
    // Clientes
    Route::apiResource('clientes', ClienteController::class);
    
    // Serviços
    Route::apiResource('servicos', ServicoController::class);
    
    // Agendamentos
    Route::apiResource('agendamentos', AgendamentoController::class);
    
    // Endpoints especiais
    Route::get('agendamentos/hoje', [AgendamentoController::class, 'hoje']);
    Route::get('agendamentos/futuros', [AgendamentoController::class, 'futuros']);
    Route::post('agendamentos/{agendamento}/confirmar', [AgendamentoController::class, 'confirmar']);
    Route::post('agendamentos/{agendamento}/cancelar', [AgendamentoController::class, 'cancelar']);
    
    // Estatísticas
    Route::get('estatisticas/barbearia/{barbearia}', [BarbeariaController::class, 'estatisticas']);
    Route::get('estatisticas/geral', [BarbeariaController::class, 'estatisticasGerais']);
});

// Rotas públicas (para integração futura)
Route::get('barbearias/{barbearia}/servicos', [ServicoController::class, 'porBarbearia']);
Route::get('barbearias/{barbearia}/horarios-disponiveis', [AgendamentoController::class, 'horariosDisponiveis']);