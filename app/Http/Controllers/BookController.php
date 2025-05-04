<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\UserGenre;
use App\Notifications\NewBookAdded;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BookController extends Controller
{
    // GET /api/books
    public function index(): JsonResponse
    {
        $books = Book::with(['author','genre'])->get();
        return response()->json(['status'=>'success','data'=>$books], 200);
    }

    // GET /api/books/{id}
    public function show(int $id): JsonResponse
    {
        $book = Book::with(['author','genre'])->findOrFail($id);
        return response()->json(['status'=>'success','data'=>$book], 200);
    }

    // GET /api/genres/{genreId}/books
    public function booksByGenre(int $genreId): JsonResponse
    {
        $list = Book::where('genre_id', $genreId)->with(['author','genre'])->get();
        return response()->json(['status'=>'success','data'=>$list], 200);
    }

    // GET /api/authors/{authorId}/books
    public function booksByAuthor(int $authorId): JsonResponse
    {
        $list = Book::where('author_id', $authorId)->with(['author','genre'])->get();
        return response()->json(['status'=>'success','data'=>$list], 200);
    }

    // POST /api/books
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'author_id'    => 'required|exists:authors,id',
            'genre_id'     => 'required|exists:genres,id',
            'published_at' => 'nullable|date',
        ]);

        $book = Book::create($data);

        // notify users who like this genre
        UserGenre::where('genre_id', $book->genre_id)
            ->with('user')
            ->get()
            ->each(fn($ug) => $ug->user->notify(new NewBookAdded($book)));

        return response()->json(['status'=>'created','data'=>$book], 201);
    }

    // PUT/PATCH /api/books/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $book = Book::findOrFail($id);
        $data = $request->validate([
            'title'        => 'sometimes|string|max:255',
            'description'  => 'nullable|string',
            'author_id'    => 'sometimes|exists:authors,id',
            'genre_id'     => 'sometimes|exists:genres,id',
            'published_at' => 'nullable|date',
        ]);
        $book->update($data);
        return response()->json(['status'=>'updated','data'=>$book], 200);
    }

    // DELETE /api/books/{id}
    public function destroy(int $id): JsonResponse
    {
        $book = Book::findOrFail($id);
        $book->delete();
        return response()->json(['status'=>'deleted','message'=>'Book removed'], 204);
    }
}
