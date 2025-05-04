<?php

namespace App\Http\Controllers;

use App\Models\UserGenre;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserGenreController extends Controller
{
    /**
     * GET /api/user-genres
     * List all UserGenre records.
     */
    public function index(): JsonResponse
    {
        $list = UserGenre::with(['user','genre'])->get();
        return response()->json([
            'status' => 'success',
            'data'   => $list,
        ], 200);
    }

    /**
     * GET /api/user-genres/{id}
     * Show a single UserGenre by its primary key.
     */
    public function show(int $id): JsonResponse
    {
        $item = UserGenre::with(['user','genre'])->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data'   => $item,
        ], 200);
    }

    /**
     * GET /api/genres/{genreId}/user-genres
     * List all UserGenre records for a specific genre.
     */
    public function byGenre(int $genreId): JsonResponse
    {
        $list = UserGenre::where('genre_id', $genreId)
            ->with(['user','genre'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $list,
        ], 200);
    }

    /**
     * GET /api/users/{userId}/user-genres
     * List all UserGenre records for a specific user.
     */
    public function byUser(int $userId): JsonResponse
    {
        $list = UserGenre::where('user_id', $userId)
            ->with(['user','genre'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $list,
        ], 200);
    }

    /**
     * POST /api/user-genres
     * Add a genre to a user's favorites.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id'  => 'required|exists:users,id',
            'genre_id' => 'required|exists:genres,id',
        ]);

        $item = UserGenre::create($data);

        return response()->json([
            'status' => 'created',
            'data'   => $item,
        ], 201);
    }

    /**
     * DELETE /api/user-genres/{id}
     * Remove a genre from a user's favorites.
     */
    public function destroy(int $id): JsonResponse
    {
        $item = UserGenre::findOrFail($id);
        $item->delete();

        return response()->json([
            'status'  => 'deleted',
            'message' => 'Removed from favorites',
        ], 204);
    }
}
