<?php

namespace Database\Seeders;

use App\Models\Author;
use Illuminate\Database\Seeder;

class AuthorSeeder extends Seeder
{
    public function run()
    {
        // Tworzy 10 autorów z losowymi danymi
        Author::factory(10)->create();

        // Przykład ręcznego dodania autora (opcjonalne)
        Author::create([
            'name' => 'J.K. Rowling',
            'biography' => 'Autorka serii o Harrym Potterze.',
            'photo' => '/storage/authors/jk-rowling.jpg'
        ]);
    }
}
