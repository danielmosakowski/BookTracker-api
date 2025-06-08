<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookRating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookRatingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_index_returns_ratings_for_book()
    {
        $book = Book::factory()->hasRatings(3)->create();

        $response = $this->getJson("/api/books/{$book->id}/ratings");
        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    public function test_show_returns_specific_rating()
    {
        $rating = BookRating::factory()->create();

        $response = $this->getJson("/api/ratings/{$rating->id}");
        $response->assertOk();
        $response->assertJsonPath('data.id', $rating->id);
    }

    public function test_store_creates_new_rating()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/books/{$book->id}/ratings", [
                'rating' => 5,
                'comment' => 'Great book!'
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('book_ratings', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 5
        ]);
    }

    public function test_update_modifies_existing_rating()
    {
        $user = User::factory()->create();
        $rating = BookRating::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->putJson("/api/ratings/{$rating->id}", [
                'rating' => 3
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('book_ratings', [
            'id' => $rating->id,
            'rating' => 3
        ]);
    }

    public function test_destroy_deletes_rating()
    {
        $user = User::factory()->create();
        $rating = BookRating::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/ratings/{$rating->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('book_ratings', ['id' => $rating->id]);
    }
}
