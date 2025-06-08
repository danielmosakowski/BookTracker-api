<?php

namespace Database\Factories;

use App\Models\Challenge;
use App\Models\User;
use App\Models\UserChallenge;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserChallengeFactory extends Factory
{
    protected $model = UserChallenge::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'challenge_id' => Challenge::factory(),
            'completed_books' => $this->faker->numberBetween(0, 10),
            'is_completed' => $this->faker->boolean,
        ];
    }
}
