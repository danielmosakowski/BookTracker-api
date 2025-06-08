<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Book;
use App\Models\UserBook;
use Illuminate\Database\Seeder;

class UserBookSeeder extends Seeder
{
    public function run(): void
    {
        // Pobierz pierwszych 10 użytkowników i książek
        $users = User::take(10)->get();
        $books = Book::take(20)->get();

        // Dla każdego użytkownika dodaj 3-5 książek do kolekcji
        $users->each(function ($user) use ($books) {
            $userBooks = $books->random(rand(3, 5))
                ->map(function ($book) {
                    return [
                        'book_id' => $book->id,
                        'status' => $this->randomStatus(),
                        'created_at' => now()->subDays(rand(1, 30)),
                    ];
                });

            $user->userBooks()->createMany($userBooks);
        });

        // Dodatkowo: jeden użytkownik z wieloma książkami do testowania paginacji
        $testUser = User::first();
        $books->take(15)->each(function ($book) use ($testUser) {
            $testUser->userBooks()->firstOrCreate([
                'book_id' => $book->id,
            ], [
                'status' => $this->randomStatus(),
            ]);
        });
    }

    private function randomStatus(): string
    {
        $statuses = ['want_to_read', 'reading', 'read'];
        return $statuses[array_rand($statuses)];
    }
}
