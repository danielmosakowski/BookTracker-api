<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(AuthorSeeder::class);
        $this->call(GenreSeeder::class);
        $this->call(UserGenreSeeder::class);
        $this->call(BookSeeder::class);
        $this->call(BookRatingSeeder::class);
    }
}
