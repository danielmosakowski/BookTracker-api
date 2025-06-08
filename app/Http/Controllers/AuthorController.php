<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthorController extends Controller
{
    // GET /api/authors
    public function index(): JsonResponse
    {
        $authors = Author::all();
        return response()->json(['status' => 'success', 'data' => $authors], 200);
    }

    // GET /api/authors/{id}
    public function show(int $id): JsonResponse
    {
        $author = Author::findOrFail($id);
        return response()->json(['status' => 'success', 'data' => $author], 200);
    }

    // POST /api/authors
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'biography' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $data = $request->only(['name', 'biography']);

        // Obsługa zdjęcia (jeśli przesłane)
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('public/authors/photos');
            $data['photo'] = Storage::url($path);
        }

        $author = Author::create($data);
        return response()->json(['status' => 'created', 'data' => $author], 201);
    }

    // PUT/PATCH /api/authors/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $author = Author::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'biography' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $data = $request->only(['name', 'biography']);

        // Aktualizacja zdjęcia (jeśli przesłane)
        if ($request->hasFile('photo')) {
            // Usuń stare zdjęcie (jeśli istnieje)
            if ($author->photo) {
                $oldPhotoPath = Str::replaceFirst('/storage', 'public', $author->photo);
                Storage::delete($oldPhotoPath);
            }

            $path = $request->file('photo')->store('public/authors/photos');
            $data['photo'] = Storage::url($path);
        }

        $author->update($data);
        return response()->json(['status' => 'updated', 'data' => $author], 200);
    }

    // DELETE /api/authors/{id}
    public function destroy(int $id): JsonResponse
    {
        $author = Author::findOrFail($id);

        // Usuń zdjęcie (jeśli istnieje)
        if ($author->photo) {
            $photoPath = Str::replaceFirst('/storage', 'public', $author->photo);
            Storage::delete($photoPath);
        }

        $author->delete();
        return response()->json(['status' => 'deleted', 'message' => 'Author removed'], 204);
    }
}
