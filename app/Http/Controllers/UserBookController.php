<?php

namespace App\Http\Controllers;

use App\Models\UserBook;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserBookController extends Controller
{
    /**
     * GET /api/user-books
     * List all books in the authenticated user's collection.
     */
    public function index(Request $request): JsonResponse
    {
        $userBooks = $request->user()
            ->userBooks()
            ->with(['book', 'readingProgress'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $userBooks,
        ]);
    }

    /**
     * GET /api/user-books/{id}
     * Show a single user-book entry.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $userBook = $request->user()
            ->userBooks()
            ->with(['book', 'user', 'readingProgress'])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $userBook,
        ]);
    }

    /**
     * GET /api/books/{bookId}/user-books
     * List all user-book entries for a specific book.
     */
    public function byBook(int $bookId): JsonResponse
    {
        $userBooks = UserBook::where('book_id', $bookId)
            ->with(['user', 'book', 'readingProgress'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $userBooks,
        ]);
    }

    /**
     * GET /api/users/{userId}/user-books
     * List all user-book entries for a specific user.
     */
    public function byUser(int $userId): JsonResponse
    {
        $userBooks = UserBook::where('user_id', $userId)
            ->with(['book', 'readingProgress'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $userBooks,
        ]);
    }

    /**
     * POST /api/user-books
     * Add a book to the authenticated user's collection.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'status' => 'sometimes|in:want_to_read,reading,read',
        ]);

        // Check if the book is already in user's collection
        if ($request->user()->userBooks()->where('book_id', $validated['book_id'])->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This book is already in your collection',
            ], 409);
        }

        $userBook = $request->user()->userBooks()->create([
            'book_id' => $validated['book_id'],
            'status' => $validated['status'] ?? 'want_to_read',
        ]);

        return response()->json([
            'status' => 'created',
            'data' => $userBook->load('book'),
        ], 201);
    }

    /**
     * PUT /api/user-books/{id}
     * Update a book in the authenticated user's collection.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:want_to_read,reading,read',
        ]);

        $userBook = $request->user()
            ->userBooks()
            ->findOrFail($id);

        $userBook->update($validated);

        return response()->json([
            'status' => 'updated',
            'data' => $userBook->load('book'),
        ]);
    }

    /**
     * DELETE /api/user-books/{id}
     * Remove a book from the authenticated user's collection.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $userBook = $request->user()
            ->userBooks()
            ->findOrFail($id);

        $userBook->delete();

        return response()->json([
            'status' => 'deleted',
            'message' => 'Book removed from your collection',
        ], 204);
    }
}
