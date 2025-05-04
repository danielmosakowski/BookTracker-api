<?php

namespace App\Http\Controllers;

use App\Models\UserBook;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserBookController extends Controller
{
    /**
     * GET /api/user-books
     * List all books in the authenticated user’s collection.
     */
    public function index(Request $request): JsonResponse
    {
        $list = $request->user()
            ->userBooks()   // assumes User model has: public function userBooks() { return $this->hasMany(UserBook::class); }
            ->with('book')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $list,
        ], 200);
    }

    /**
     * GET /api/user-books/{id}
     * Show a single entry by its primary key.
     */
    public function show(int $id): JsonResponse
    {
        $item = UserBook::with(['book','user'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $item,
        ], 200);
    }

    /**
     * GET /api/books/{bookId}/user-books
     * List all user‑book entries for a specific book.
     */
    public function byBook(int $bookId): JsonResponse
    {
        $list = UserBook::where('book_id', $bookId)
            ->with(['user','book'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $list,
        ], 200);
    }

    /**
     * GET /api/users/{userId}/user-books
     * List all user‑book entries for a specific user.
     */
    public function byUser(int $userId): JsonResponse
    {
        $list = UserBook::where('user_id', $userId)
            ->with(['user','book'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $list,
        ], 200);
    }

    /**
     * POST /api/user-books
     * Add a book to the authenticated user’s collection.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'book_id' => 'required|exists:books,id',
        ]);

        $data['user_id'] = $request->user()->id;

        $item = UserBook::create($data);

        return response()->json([
            'status' => 'created',
            'data'   => $item,
        ], 201);
    }

    /**
     * DELETE /api/user-books/{id}
     * Remove a book from the authenticated user’s collection.
     * (Role/ownership checks to be added later.)
     */
    public function destroy(int $id): JsonResponse
    {
        $item = UserBook::findOrFail($id);
        $item->delete();

        return response()->json([
            'status'  => 'deleted',
            'message' => 'Removed from collection',
        ], 204);
    }
}
