<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $regularUser;
    protected $genre;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['is_admin' => true]);
        $this->regularUser = User::factory()->create(['is_admin' => false]);
        $this->genre = Genre::factory()->create();
    }

    // ------------------------------------------
    // Testy publicznych endpointów (GET)
    // ------------------------------------------
    public function test_guest_can_view_all_genres()
    {
        $response = $this->getJson('/api/genres');
        $response->assertStatus(200);
    }

    public function test_guest_can_view_books_for_genre()
    {
        $response = $this->getJson("/api/genres/{$this->genre->id}/books");
        $response->assertStatus(200);
    }

    public function test_regular_user_can_view_all_genres()
    {
        $response = $this->actingAs($this->regularUser)
            ->getJson('/api/genres');
        $response->assertStatus(200);
    }

    public function test_regular_user_can_view_books_for_genre()
    {
        $response = $this->actingAs($this->regularUser)
            ->getJson("/api/genres/{$this->genre->id}/books");
        $response->assertStatus(200);
    }

    // ------------------------------------------
    // Testy chronionych endpointów (POST/PUT/DELETE)
    // ------------------------------------------
    public function test_admin_can_create_genre()
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/genres', [
                'name' => 'New Genre',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'New Genre');

        $this->assertDatabaseHas('genres', ['name' => 'New Genre']);
    }

    public function test_admin_can_update_genre()
    {
        $response = $this->actingAs($this->adminUser)
            ->putJson("/api/genres/{$this->genre->id}", [
                'name' => 'Updated Genre',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Genre');

        $this->assertDatabaseHas('genres', [
            'id' => $this->genre->id,
            'name' => 'Updated Genre',
        ]);
    }

    public function test_admin_can_delete_genre()
    {
        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/api/genres/{$this->genre->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'deleted',
            'message' => 'Genre removed successfully',
        ]);
        $this->assertDatabaseMissing('genres', ['id' => $this->genre->id]);
    }

    public function test_regular_user_cannot_create_genre()
    {
        $response = $this->actingAs($this->regularUser)
            ->postJson('/api/genres', [
                'name' => 'Not Allowed',
            ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Forbidden. Admins only.']);
    }

    public function test_regular_user_cannot_update_genre()
    {
        $response = $this->actingAs($this->regularUser)
            ->putJson("/api/genres/{$this->genre->id}", [
                'name' => 'Hacked',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('genres', [
            'id' => $this->genre->id,
            'name' => $this->genre->name,
        ]);
    }

    public function test_regular_user_cannot_delete_genre()
    {
        $response = $this->actingAs($this->regularUser)
            ->deleteJson("/api/genres/{$this->genre->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('genres', ['id' => $this->genre->id]);
    }

    // ------------------------------------------
    // Testy niezalogowanego użytkownika
    // ------------------------------------------
    public function test_guest_cannot_create_genre()
    {
        $response = $this->postJson('/api/genres', [
            'name' => 'Guest Genre',
        ]);

        $response->assertStatus(401);
    }

    public function test_guest_cannot_update_genre()
    {
        $response = $this->putJson("/api/genres/{$this->genre->id}", [
            'name' => 'Guest Update',
        ]);

        $response->assertStatus(401);
    }

    public function test_guest_cannot_delete_genre()
    {
        $response = $this->deleteJson("/api/genres/{$this->genre->id}");
        $response->assertStatus(401);
    }
}
