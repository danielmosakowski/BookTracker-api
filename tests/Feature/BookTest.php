<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_index_returns_all_books()
    {
        Book::factory()->count(3)->create();

        $response = $this->getJson('/api/books');
        $response->assertOk();

        $booksCount = Book::count();
        $response->assertJsonCount($booksCount, 'data');
    }

    public function test_show_returns_specific_book()
    {
        $book = Book::factory()->create();

        $response = $this->getJson("/api/books/{$book->id}");
        $response->assertOk();
        $response->assertJsonPath('data.id', $book->id);
    }

    public function test_books_by_genre()
    {
        $genre = Genre::factory()->create();
        Book::factory()->count(2)->create(['genre_id' => $genre->id]);
        Book::factory()->count(3)->create();

        $response = $this->getJson("/api/genres/{$genre->id}/books");
        $response->assertOk();
        $response->assertJsonCount(2, 'data');
    }

    public function test_books_by_author()
    {
        $author = Author::factory()->create();
        Book::factory()->count(2)->create(['author_id' => $author->id]);
        Book::factory()->count(3)->create();

        $response = $this->getJson("/api/authors/{$author->id}/books");
        $response->assertOk();
        $response->assertJsonCount(2, 'data');
    }

    public function test_store_creates_new_book()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);
        $this->actingAs($user, 'sanctum');

        $author = Author::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->postJson('/api/books', [
            'title' => 'Test Book',
            'author_id' => $author->id,
            'genre_id' => $genre->id,
            'isbn' => '1234567890',
            'published_year' => 2023,
            'description' => 'Test description',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('books', ['title' => 'Test Book']);
    }

    public function test_update_modifies_existing_book()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);
        $this->actingAs($user, 'sanctum');

        $book = Book::factory()->create(['title' => 'Old Title']);

        $response = $this->putJson("/api/books/{$book->id}", [
            'title' => 'New Title'
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'New Title']);
    }

    public function test_destroy_deletes_book()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);
        $this->actingAs($user, 'sanctum');

        $book = Book::factory()->create();

        $response = $this->deleteJson("/api/books/{$book->id}");
        $response->assertNoContent();
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }
}
