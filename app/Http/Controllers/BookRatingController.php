<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookRating;
use App\Models\UserGenre;
use App\Notifications\NewCommentOnBook;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BookRatingController extends Controller
{
    // GET /api/books/{bookId}/ratings
    public function index(int $bookId): JsonResponse
    {
        $book = Book::findOrFail($bookId);
        $ratings = $book->bookRatings()->with('user')->get();
        return response()->json(['status'=>'success','data'=>$ratings], 200);
    }

    // GET /api/ratings/{id}
    public function show(int $id): JsonResponse
    {
        $rating = BookRating::with(['user','book'])->findOrFail($id);
        return response()->json(['status'=>'success','data'=>$rating], 200);
    }

    // POST /api/books/{bookId}/ratings
    public function store(Request $request, int $bookId): JsonResponse
    {
        $book = Book::findOrFail($bookId);
        $data = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);
        $data['user_id'] = auth()->id();

        $rating = $book->bookRatings()->create($data);

        if (!empty($data['comment'])) {
            UserGenre::where('genre_id', $book->genre_id)
                ->with('user')
                ->get()
                ->each(fn($ug) => $ug->user->notify(new NewCommentOnBook($book, $rating)));
        }

        return response()->json(['status'=>'created','data'=>$rating], 201);
    }

    // PUT/PATCH /api/ratings/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $rating = BookRating::findOrFail($id);
        $data = $request->validate([
            'rating'  => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);
        $rating->update($data);
        return response()->json(['status'=>'updated','data'=>$rating], 200);
    }

    // DELETE /api/ratings/{id}
    public function destroy(int $id): JsonResponse
    {
        $rating = BookRating::findOrFail($id);
        $rating->delete();
        return response()->json(['status'=>'deleted','message'=>'Removed'], 204);
    }
}
