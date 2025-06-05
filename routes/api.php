<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::prefix('auth')->group(function () {
    // Publiczne endpointy
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);

    // Chronione endpointy
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [UserController::class, 'logout']);
        Route::get('/me', [UserController::class, 'me']);
        Route::put('/user', [UserController::class, 'update']); // Dodana trasa update

        // Endpointy tylko dla admina
        Route::middleware('admin')->group(function () {
            Route::put('/users/{user}', [UserController::class, 'updateUser']);
            Route::delete('/users/{user}', [UserController::class, 'destroyUser']);
        });
    });
});
