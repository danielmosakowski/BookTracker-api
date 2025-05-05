<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Grupa tras związanych z autoryzacją użytkownika (bez potrzeby autoryzacji)
Route::prefix('auth')->group(function () {
    // Rejestracja użytkownika
    Route::post('/register', [UserController::class, 'register']);

    // Logowanie użytkownika
    Route::post('/login', [UserController::class, 'login']);
});

// Grupa tras użytkownika (wymaga autoryzacji)
Route::middleware('auth:sanctum')->prefix('user')->group(function () {
    // Pobieranie danych użytkownika (dane zalogowanego użytkownika)
    Route::get('/me', [UserController::class, 'me']);

    // Zmiana hasła użytkownika
    Route::post('/change-password', [UserController::class, 'changePassword']);

    // Wylogowanie użytkownika
    Route::post('/logout', [UserController::class, 'logout']);
});
