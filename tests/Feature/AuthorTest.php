<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthorTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $regularUser;
    protected $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['is_admin' => true]);
        $this->regularUser = User::factory()->create(['is_admin' => false]);
        $this->author = Author::factory()->create();
    }

    // ------------------------------------------
    // Testy publicznych endpointów (GET)
    // ------------------------------------------
    public function test_regular_user_can_view_all_authors()
    {
        $response = $this->actingAs($this->regularUser)
            ->getJson('/api/authors');

        $response->assertStatus(200);
    }

    public function test_guest_can_view_all_authors()
    {
        $response = $this->getJson('/api/authors');
        $response->assertStatus(200);
    }

    public function test_regular_user_can_view_single_author()
    {
        $response = $this->actingAs($this->regularUser)
            ->getJson("/api/authors/{$this->author->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $this->author->id);
    }

    // ------------------------------------------
    // Testy chronionych endpointów (tylko admin)
    // ------------------------------------------
    public function test_regular_user_cannot_create_author()
    {
        $response = $this->actingAs($this->regularUser)
            ->postJson('/api/authors', [
                'name' => 'New Author',
                'biography' => 'Bio'
            ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Forbidden. Admins only.']);
    }

    public function test_regular_user_cannot_update_author()
    {
        $response = $this->actingAs($this->regularUser)
            ->putJson("/api/authors/{$this->author->id}", [
                'name' => 'Updated Name'
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('authors', [
            'id' => $this->author->id,
            'name' => $this->author->name // Nazwa nie powinna się zmienić
        ]);
    }

    public function test_regular_user_cannot_delete_author()
    {
        $response = $this->actingAs($this->regularUser)
            ->deleteJson("/api/authors/{$this->author->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('authors', ['id' => $this->author->id]);
    }

    public function test_regular_user_cannot_upload_author_photo()
    {
        Storage::fake('public');

        $response = $this->actingAs($this->regularUser)
            ->postJson('/api/authors', [
                'name' => 'Author',
                'photo' => UploadedFile::fake()->image('photo.jpg')
            ]);

        $response->assertStatus(403);
    }

    // ------------------------------------------
    // Testy dla niezalogowanych użytkowników (guest)
    // ------------------------------------------
    public function test_guest_cannot_create_author()
    {
        $response = $this->postJson('/api/authors', [
            'name' => 'Guest Author'
        ]);

        $response->assertStatus(401);
    }

    public function test_guest_cannot_update_author()
    {
        $response = $this->putJson("/api/authors/{$this->author->id}", [
            'name' => 'Guest Update Attempt'
        ]);

        $response->assertStatus(401);
    }

    public function test_guest_cannot_delete_author()
    {
        $response = $this->deleteJson("/api/authors/{$this->author->id}");
        $response->assertStatus(401);
    }
}
