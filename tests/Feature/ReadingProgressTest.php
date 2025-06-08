<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ReadingProgress;
use App\Models\User;
use App\Models\UserBook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingProgressTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Book $book;
    protected UserBook $userBook;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->book = Book::factory()->create(['total_pages' => 300]);
        $this->userBook = UserBook::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'status' => 'reading'
        ]);
    }

    public function test_show_returns_progress()
    {
        // Create progress first
        $progress = ReadingProgress::factory()->create([
            'user_book_id' => $this->userBook->id,
            'current_page' => 50
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/user-books/{$this->userBook->id}/progress");

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'data' => ['id', 'current_page', 'user_book_id', 'created_at', 'updated_at']
        ]);
    }

    public function test_show_returns_default_structure_when_no_progress()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/user-books/{$this->userBook->id}/progress");

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'id' => null,
                'current_page' => 0,
                'user_book_id' => $this->userBook->id,
                'created_at' => null,
                'updated_at' => null
            ]
        ]);
    }

    public function test_store_creates_new_progress()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/user-books/{$this->userBook->id}/progress", [
                'current_page' => 50
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('reading_progresses', [
            'user_book_id' => $this->userBook->id,
            'current_page' => 50
        ]);
    }

    public function test_store_returns_error_when_progress_exists()
    {
        ReadingProgress::factory()->create(['user_book_id' => $this->userBook->id]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/user-books/{$this->userBook->id}/progress", [
                'current_page' => 50
            ]);

        $response->assertStatus(409);
    }

    public function test_update_creates_progress_when_not_exists()
    {
        $response = $this->actingAs($this->user)
            ->putJson("/api/user-books/{$this->userBook->id}/progress", [
                'current_page' => 50
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('reading_progresses', [
            'user_book_id' => $this->userBook->id,
            'current_page' => 50
        ]);
    }

    public function test_update_updates_existing_progress()
    {
        $progress = ReadingProgress::factory()->create([
            'user_book_id' => $this->userBook->id,
            'current_page' => 10
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/user-books/{$this->userBook->id}/progress", [
                'current_page' => 50
            ]);

        $response->assertOk();
        $this->assertEquals(50, $progress->fresh()->current_page);
    }

    public function test_update_marks_as_finished()
    {
        $progress = ReadingProgress::factory()->create([
            'user_book_id' => $this->userBook->id
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/user-books/{$this->userBook->id}/progress", [
                'current_page' => 300,
                'finished' => true
            ]);

        $response->assertOk();
        $this->assertEquals('completed', $this->userBook->fresh()->status);
    }


    public function test_destroy_deletes_progress()
    {
        $progress = ReadingProgress::factory()->create([
            'user_book_id' => $this->userBook->id
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/progress/{$progress->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('reading_progresses', ['id' => $progress->id]);
    }

    public function test_destroy_fails_for_other_users_progress()
    {
        $otherUser = User::factory()->create();
        $otherUserBook = UserBook::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $this->book->id
        ]);
        $progress = ReadingProgress::factory()->create([
            'user_book_id' => $otherUserBook->id
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/progress/{$progress->id}");

        $response->assertNotFound();
    }
}
