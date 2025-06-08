<?php

namespace Database\Seeders;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChallengeSeeder extends Seeder
{
    public function run(): void
    {
        // Create 5 challenges
        $challenges = Challenge::factory()->count(5)->create();

        // Attach random challenges to users
        User::all()->each(function ($user) use ($challenges) {
            $user->challenges()->attach(
                $challenges->random(rand(1, 3))->pluck('id')->toArray(),
                [
                    'completed_books' => rand(0, 5),
                    'is_completed' => rand(0, 1)
                ]
            );
        });
    }
}
