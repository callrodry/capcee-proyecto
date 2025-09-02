<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ArchivoController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\ReporteController;
use App\Http\Controllers\Api\AuthController;

Route::get('/', function () {
    return "hola que hace";
});
// Rutas públicas
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    
    // Autenticación
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Archivos
    Route::prefix('archivos')->group(function () {
        Route::get('/', [ArchivoController::class, 'index']);
        Route::post('/upload', [ArchivoController::class, 'upload']);
        Route::get('/{id}', [ArchivoController::class, 'show']);
        Route::get('/{id}/estado', [ArchivoController::class, 'estado']);
        Route::get('/{id}/preview', [ArchivoController::class, 'preview']);
        Route::post('/{id}/reintento', [ArchivoController::class, 'reintento']);
        Route::delete('/{id}', [ArchivoController::class, 'destroy']);
    });
    
    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/metricas', [DashboardController::class, 'metricas']);
        Route::get('/departamento/{id}/estadisticas', [DashboardController::class, 'estadisticasDepartamento']);
    });
    
    // Departamentos
    Route::prefix('departamentos')->group(function () {
        Route::get('/', [DepartmentController::class, 'index']);
        Route::get('/{id}/archivos', [DepartmentController::class, 'archivos']);
    });
    
    // Reportes
    Route::prefix('reportes')->group(function () {
        Route::get('/diario', [ReporteController::class, 'diario']);
        Route::get('/departamental', [ReporteController::class, 'departamental']);
        Route::get('/productividad', [ReporteController::class, 'productividad']);
    });
});