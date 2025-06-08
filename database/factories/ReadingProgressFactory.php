<?php

namespace Database\Factories;

use App\Models\UserBook;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReadingProgressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_book_id' => UserBook::factory(),
            'current_page' => $this->faker->numberBetween(0, 500),
            'updated_at' => $this->faker->dateTimeThisYear()
        ];
    }
}
