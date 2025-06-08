<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserGenre;

class UserGenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Dla każdego użytkownika dodaj 3-5 ulubionych gatunków
        User::all()->each(function ($user) {
            UserGenre::factory()
                ->count(rand(3, 5))
                ->create(['user_id' => $user->id]);
        });
    }
}
