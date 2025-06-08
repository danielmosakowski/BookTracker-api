<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GenreController extends Controller
{
    // GET /api/genres
    public function index(): JsonResponse
    {
        $genres = Genre::all();
        return response()->json([
            'status' => 'success',
            'data' => $genres
        ], 200);
    }

    // GET /api/genres/{id}
    public function show(int $id): JsonResponse
    {
        $genre = Genre::findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $genre
        ], 200);
    }

    // GET /api/genres/{id}/books
    public function books(int $id): JsonResponse
    {
        $genre = Genre::findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $genre->books
        ], 200);
    }

    // POST /api/genres
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:genres',
            'description' => 'nullable|string' // Dodane pole description
        ]);

        $genre = Genre::create($validated);
        return response()->json([
            'status' => 'created',
            'data' => $genre
        ], 201);
    }

    // PUT/PATCH /api/genres/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $genre = Genre::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:genres,name,'.$genre->id,
            'description' => 'nullable|string' // Dodane pole description
        ]);

        $genre->update($validated);
        return response()->json([
            'status' => 'updated',
            'data' => $genre
        ], 200);
    }

    // DELETE /api/genres/{id}
    public function destroy(int $id): JsonResponse
    {
        $genre = Genre::findOrFail($id);
        $genre->delete();
        return response()->json([
            'status' => 'deleted',
            'message' => 'Genre removed successfully'
        ], 200); // Zmienione na 200 zamiast 204 aby zwracać message
    }
}
