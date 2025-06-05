<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AuthorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'biography' => $this->faker->paragraph,
            'photo' => $this->faker->optional()->imageUrl(200, 200, 'people')
        ];
    }

    public function withPhoto(): static
    {
        return $this->state([
            'photo' => '/storage/authors/' . $this->faker->uuid . '.jpg'
        ]);
    }
}
