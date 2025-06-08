<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Genre;
use App\Models\UserGenre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserGenreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_index_returns_all_user_genres()
    {
        $response = $this->getJson('/api/user-genres');
        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'data' => [
                '*' => [
                    'id',
                    'user_id',
                    'genre_id',
                    'created_at',
                    'updated_at',
                    'user',
                    'genre',
                ]
            ]
        ]);
    }

    public function test_store_creates_new_user_genre()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/user-genres', [
                'user_id' => $user->id,
                'genre_id' => $genre->id,
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('user_genres', [
            'user_id' => $user->id,
            'genre_id' => $genre->id,
        ]);
    }

    public function test_store_fails_when_duplicate()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        UserGenre::factory()->create([
            'user_id' => $user->id,
            'genre_id' => $genre->id,
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/user-genres', [
                'user_id' => $user->id,
                'genre_id' => $genre->id,
            ]);

        $response->assertUnprocessable();
    }

    public function test_destroy_removes_user_genre()
    {
        $user = User::factory()->create();
        $userGenre = UserGenre::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/user-genres/{$userGenre->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('user_genres', ['id' => $userGenre->id]);
    }

    public function test_byUser_returns_genres_for_specific_user()
    {
        $user = User::factory()->create();
        UserGenre::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/users/{$user->id}/user-genres");
        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    public function test_byGenre_returns_users_for_specific_genre()
    {
        $genre = Genre::factory()->create();
        UserGenre::factory()->count(3)->create(['genre_id' => $genre->id]);

        $response = $this->getJson("/api/genres/{$genre->id}/user-genres");
        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }
}
