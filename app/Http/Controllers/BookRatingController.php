<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookRating;
use App\Models\UserGenre;
use App\Notifications\NewCommentOnBook;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BookRatingController extends Controller
{
    // GET /api/books/{bookId}/ratings
    public function index(int $bookId): JsonResponse
    {
        $ratings = BookRating::where('book_id', $bookId)
            ->with(['user', 'book'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $ratings
        ]);
    }

    // GET /api/ratings/{id}
    public function show(int $id): JsonResponse
    {
        $rating = BookRating::with(['user', 'book'])->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $rating
        ]);
    }

    // POST /api/books/{bookId}/ratings
    public function store(Request $request, int $bookId): JsonResponse
    {
        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $book = Book::findOrFail($bookId);

        $rating = $book->ratings()->create([
            'rating' => $data['rating'],
            'review' => $data['comment'] ?? null,
            'user_id' => Auth::id()
        ]);

        if (!empty($data['comment'])) {
            UserGenre::where('genre_id', $book->genre_id)
                ->with('user')
                ->get()
                ->each(function ($userGenre) use ($book, $rating) {
                    $userGenre->user->notify(new NewCommentOnBook($book, $rating));
                });
        }

        return response()->json([
            'status' => 'created',
            'data' => $rating->load('user')
        ], 201);
    }

    // PUT/PATCH /api/ratings/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $rating = BookRating::where('user_id', Auth::id())->findOrFail($id);

        $data = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $rating->update([
            'rating' => $data['rating'] ?? $rating->rating,
            'review' => $data['comment'] ?? $rating->review
        ]);

        return response()->json([
            'status' => 'updated',
            'data' => $rating->fresh()
        ]);
    }

    // DELETE /api/ratings/{id}
    public function destroy(int $id): JsonResponse
    {
        $rating = BookRating::where('user_id', Auth::id())->findOrFail($id);
        $rating->delete();

        return response()->json(null, 204);
    }
}
