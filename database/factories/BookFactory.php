<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Genre;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'author_id' => Author::factory(),
            'genre_id' => Genre::factory(),
            'isbn' => $this->faker->unique()->isbn13,
            'description' => $this->faker->paragraph,
            'cover_image' => $this->faker->imageUrl(200, 300, 'books'),
            'published_year' => $this->faker->year,
            'total_pages' => $this->faker->numberBetween(100, 500),
        ];
    }
}
