<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\DocumentsController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('me', [AuthController::class, 'me'])->name('me');

    Route::get('categories', [CategoriesController::class, 'index'])->name('categories.index');

    // CRUD dokumen — authorization Gate/Policy di dalam DocumentsController.
    // Contoh middleware role (guard di lapisan route), biarkan di-comment untuk demo Gate/Policy:
    Route::delete('documents/{document}', [DocumentsController::class, 'destroy'])
        ->middleware('role:admin')
        ->name('documents.destroy');
    Route::apiResource('documents', DocumentsController::class);
});
