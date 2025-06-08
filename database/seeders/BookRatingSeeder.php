<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookRating;
use Illuminate\Database\Seeder;

class BookRatingSeeder extends Seeder
{
    public function run(): void
    {
        // Pobierz wszystkie książki i dla każdej dodaj losową liczbę ocen
        Book::all()->each(function ($book) {
            BookRating::factory()
                ->count(rand(1, 10)) // 1-10 ocen na książkę
                ->create(['book_id' => $book->id]);
        });
    }
}
