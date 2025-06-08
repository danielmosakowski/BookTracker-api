<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\UserGenre;
use App\Notifications\NewBookAdded;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    // GET /api/books
    public function index(): JsonResponse
    {
        $books = Book::with(['author','genre'])->get();
        return response()->json(['status'=>'success','data'=>$books]);
    }

    // GET /api/books/{id}
    public function show(int $id): JsonResponse
    {
        $book = Book::with(['author','genre'])->findOrFail($id);
        return response()->json(['status'=>'success','data'=>$book]);
    }

    // GET /api/genres/{genreId}/books
    public function booksByGenre(int $genreId): JsonResponse
    {
        $books = Book::where('genre_id', $genreId)
            ->with(['author','genre'])
            ->get();

        return response()->json(['status'=>'success','data'=>$books]);
    }

    // GET /api/authors/{authorId}/books
    public function booksByAuthor(int $authorId): JsonResponse
    {
        $books = Book::where('author_id', $authorId)
            ->with(['author','genre'])
            ->get();

        return response()->json(['status'=>'success','data'=>$books]);
    }

    // POST /api/books
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'author_id' => 'required|exists:authors,id',
            'genre_id' => 'required|exists:genres,id',
            'isbn' => 'nullable|string|unique:books,isbn',
            'cover_image' => 'nullable|url',
            'published_year' => 'nullable|integer|min:1900|max:'. date('Y'),
            'total_pages' => 'nullable|integer|min:1',
        ]);

        $book = Book::create($data);

        // Powiadom użytkowników którzy lubią ten gatunek
        UserGenre::where('genre_id', $book->genre_id)
            ->with('user')
            ->get()
            ->each(function($userGenre) use ($book) {
                $userGenre->user->notify(new NewBookAdded($book));
            });

        return response()->json([
            'status' => 'created',
            'data' => $book
        ], 201);
    }

    // PUT/PATCH /api/books/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $book = Book::findOrFail($id);

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'author_id' => 'sometimes|exists:authors,id',
            'genre_id' => 'sometimes|exists:genres,id',
            'isbn' => 'nullable|string|unique:books,isbn,'.$book->id,
            'cover_image' => 'nullable|url',
            'published_year' => 'nullable|integer|min:1900|max:'. date('Y'),
            'total_pages' => 'nullable|integer|min:1',
        ]);

        $book->update($data);

        return response()->json([
            'status' => 'updated',
            'data' => $book
        ]);
    }

    // DELETE /api/books/{id}
    public function destroy(int $id): JsonResponse
    {
        $book = Book::findOrFail($id);
        $book->delete();

        return response()->json(null, 204);
    }
}
