<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserBookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
            //'status' => $this->faker->randomElement(['reading', 'planned', 'completed']),
            'status' => $this->faker->randomElement(['want_to_read', 'reading', 'read']),
        ];
    }
}
