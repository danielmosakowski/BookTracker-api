<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::prefix('auth')->group(function () {
    // Publiczne endpointy
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);

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
