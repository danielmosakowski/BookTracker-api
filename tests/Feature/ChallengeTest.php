<?php

namespace Tests\Feature;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChallengeTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_get_all_challenges(): void
    {
        Challenge::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/challenges');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_create_challenge_as_admin(): void
    {
        $data = [
            'name' => 'Summer Reading',
            'description' => 'Read 10 books during summer',
            'target_books' => 10,
            'start_date' => '2023-06-01',
            'end_date' => '2023-08-31',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/challenges', $data);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Challenge created successfully']);

        $this->assertDatabaseHas('challenges', ['name' => 'Summer Reading']);
    }

    public function test_join_challenge(): void
    {
        $challenge = Challenge::factory()->create();

        $response = $this->actingAs($this->user)
            ->postJson("/api/challenges/{$challenge->id}/join");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Successfully joined the challenge']);

        $this->assertDatabaseHas('user_challenges', [
            'user_id' => $this->user->id,
            'challenge_id' => $challenge->id
        ]);
    }

    public function test_check_challenge_progress(): void
    {
        $challenge = Challenge::factory()->create();
        $this->user->challenges()->attach($challenge->id, [
            'completed_books' => 3,
            'is_completed' => false
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/challenges/{$challenge->id}/progress");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'completed_books' => 3,
                    'is_completed' => false
                ]
            ]);
    }
}
