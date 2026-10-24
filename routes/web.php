<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| RUTAS ORIGINALES DEL SISTEMA (DESHABILITADAS TEMPORALMENTE)
|--------------------------------------------------------------------------
|
| use App\Http\Controllers\ClientController;
| use App\Http\Controllers\ProfileController;
|
| // Rutas protegidas por autenticación
| Route::middleware(['auth'])->group(function () {
|     // Panel principal / Dashboard (Apunta al índice de ClientController)
|     Route::get('/', [ClientController::class, 'index'])->name('dashboard');
|     Route::get('/dashboard', [ClientController::class, 'index'])->name('dashboard.alt');
|
|     // Clientes
|     Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
|     Route::post('/clients/{id}/attend', [ClientController::class, 'attend'])->name('clients.attend');
|     Route::post('/clients/{client}/toggle-status', [ClientController::class, 'toggleStatus'])->name('clients.toggleStatus');
|
|     // Edición de Clientes
|     Route::get('/clients/{id}/edit', [ClientController::class, 'edit'])->name('clients.edit');
|     Route::put('/clients/{id}', [ClientController::class, 'update'])->name('clients.update');
|
|     // Reportes y Recibos (Aquí está la ruta 'report' que causaba el error)
|     Route::get('/report', [ClientController::class, 'report'])->name('report');
|     Route::get('/receipt/{recordId}', [ClientController::class, 'receipt'])->name('clients.receipt');
|     Route::get('/service-history/{record}/receipt', [ClientController::class, 'showReceipt'])->name('service.receipt');
|     Route::get('/documents/a4', [ClientController::class, 'showA4Document'])->name('documents.a4');
|
|     // Perfil de usuario (si usas Laravel Breeze/Jetstream)
|     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
|     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
|     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
|
|     // Rutas para Proformas
|     Route::post('/clients/{client}/proforma', [ClientController::class, 'storeProforma'])->name('proformas.store');
|     Route::get('/clients/{client}/proforma-data', [ClientController::class, 'getProforma'])->name('proformas.data');
|     Route::post('/clients/{client}/attend-detailed', [ClientController::class, 'attendDetailed'])->name('clients.attendDetailed');
| });
|
| // Rutas de autenticación generadas por Laravel (Login, Registro, etc.)
| require __DIR__.'/auth.php';
|
*/

// =========================================================================
// RUTA DE BLOQUEO TEMPORAL (RESPUESTA 404 NATIVA EN TODO EL SISTEMA)
// =========================================================================

Route::fallback(function () {
    abort(404);
});