<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthorController;

Route::prefix('auth')->group(function () {
    // Publiczne endpointy
    Route::post('/register', [UserController::class, 'register']);
    //Route::post('/login', [UserController::class, 'login']);
    Route::post('/login', [UserController::class, 'login'])->middleware('throttle:10,1');

    // Weryfikacja e-maila
    Route::get('/email/verify/{id}/{hash}', [UserController::class, 'verifyEmail'])
        ->middleware(['signed'])
        ->name('verification.verify');

    Route::post('/email/resend', [UserController::class, 'resendVerificationEmail'])
        ->middleware(['auth:sanctum', 'throttle:6,1']);

    // Endpointy admina (dostępne bez weryfikacji email)
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::put('/users/{user}', [UserController::class, 'updateUser']);
        Route::delete('/users/{user}', [UserController::class, 'destroyUser']);
    });

    // Chronione endpointy (wymagają weryfikacji email)
    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::post('/logout', [UserController::class, 'logout']);
        Route::get('/me', [UserController::class, 'me']);
        Route::put('/user', [UserController::class, 'update']);
    });
});



// AUTHORS
Route::prefix('auth')->group(function () {
    // ... (poprzednie endpointy auth pozostają bez zmian)
});

// Publiczne endpointy (dostęp bez logowania)
Route::get('/authors', [AuthorController::class, 'index']);
Route::get('/authors/{id}', [AuthorController::class, 'show']);

// Chronione endpointy (wymagają logowania)
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/authors', [AuthorController::class, 'store']);
    Route::put('/authors/{id}', [AuthorController::class, 'update']);
    Route::delete('/authors/{id}', [AuthorController::class, 'destroy']);
});
