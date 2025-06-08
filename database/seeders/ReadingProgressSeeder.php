<?php

namespace Database\Seeders;

use App\Models\UserBook;
use Illuminate\Database\Seeder;

class ReadingProgressSeeder extends Seeder
{
    public function run(): void
    {
        UserBook::all()->each(function ($userBook) {
            if (rand(0, 1)) {
                $userBook->readingProgress()->create([
                    'current_page' => rand(0, $userBook->book->total_pages ?? 300),
                    'updated_at' => now()->subDays(rand(0, 30))
                ]);
            }
        });
    }
}
