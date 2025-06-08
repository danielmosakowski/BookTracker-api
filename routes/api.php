<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\UserGenreController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookRatingController;
use App\Http\Controllers\ReadingProgressController;
use App\Http\Controllers\UserBookController;


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





// Publiczne endpointy (dostęp bez logowania)
Route::get('/authors', [AuthorController::class, 'index']);
Route::get('/authors/{id}', [AuthorController::class, 'show']);

// Chronione endpointy (wymagają logowania)
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/authors', [AuthorController::class, 'store']);
    Route::put('/authors/{id}', [AuthorController::class, 'update']);
    Route::delete('/authors/{id}', [AuthorController::class, 'destroy']);
});




// Publiczne endpointy (dostęp bez logowania)
Route::get('/genres', [GenreController::class, 'index']);
Route::get('/genres/{id}', [GenreController::class, 'show']);
Route::get('/genres/{id}/books', [GenreController::class, 'books']);

// Chronione endpointy (wymagają logowania i uprawnień admina)
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/genres', [GenreController::class, 'store']);
    Route::put('/genres/{id}', [GenreController::class, 'update']);
    Route::delete('/genres/{id}', [GenreController::class, 'destroy']);
});





// UserGenre endpoints
Route::get('/user-genres', [UserGenreController::class, 'index']);
Route::get('/user-genres/{id}', [UserGenreController::class, 'show']);
Route::get('/genres/{genreId}/user-genres', [UserGenreController::class, 'byGenre']);
Route::get('/users/{userId}/user-genres', [UserGenreController::class, 'byUser']);

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/user-genres', [UserGenreController::class, 'store']);
    Route::delete('/user-genres/{id}', [UserGenreController::class, 'destroy']);
});




// Book endpoints
Route::get('/books', [BookController::class, 'index']);
Route::get('/books/{id}', [BookController::class, 'show']);
Route::get('/genres/{genreId}/books', [BookController::class, 'booksByGenre']);
Route::get('/authors/{authorId}/books', [BookController::class, 'booksByAuthor']);

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/books', [BookController::class, 'store']);
    Route::put('/books/{id}', [BookController::class, 'update']);
    Route::delete('/books/{id}', [BookController::class, 'destroy']);
});



// BookRating endpoints
Route::get('/books/{bookId}/ratings', [BookRatingController::class, 'index']);
Route::get('/ratings/{id}', [BookRatingController::class, 'show']);

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/books/{bookId}/ratings', [BookRatingController::class, 'store']);
    Route::put('/ratings/{id}', [BookRatingController::class, 'update']);
    Route::delete('/ratings/{id}', [BookRatingController::class, 'destroy']);
});




Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // ReadingProgress routes
    Route::get('/user-books/{userBookId}/progress', [ReadingProgressController::class, 'show']);
    Route::post('/user-books/{userBookId}/progress', [ReadingProgressController::class, 'store']);
    Route::put('/user-books/{userBookId}/progress', [ReadingProgressController::class, 'update']);
    Route::delete('/progress/{id}', [ReadingProgressController::class, 'destroy']);
});



Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // UserBook endpoints
    Route::get('/user-books', [UserBookController::class, 'index']);
    Route::get('/user-books/{id}', [UserBookController::class, 'show']);
    Route::get('/books/{bookId}/user-books', [UserBookController::class, 'byBook']);
    Route::get('/users/{userId}/user-books', [UserBookController::class, 'byUser']);
    Route::post('/user-books', [UserBookController::class, 'store']);
    Route::put('/user-books/{id}', [UserBookController::class, 'update']);
    Route::delete('/user-books/{id}', [UserBookController::class, 'destroy']);
});
