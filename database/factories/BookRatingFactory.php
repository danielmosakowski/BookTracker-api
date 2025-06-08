<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookRatingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'rating' => $this->faker->numberBetween(1, 5),
            'review' => $this->faker->optional()->paragraph,
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
        ];
    }
}
