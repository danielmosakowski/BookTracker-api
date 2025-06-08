<?php

namespace Database\Factories;

use App\Models\Challenge;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChallengeFactory extends Factory
{
    protected $model = Challenge::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('now', '+1 month');
        $endDate = $this->faker->dateTimeBetween($startDate, '+3 months');

        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'target_books' => $this->faker->numberBetween(1, 20),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }
}
