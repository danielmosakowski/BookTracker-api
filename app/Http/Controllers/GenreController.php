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
        return response()->json(['status'=>'success','data'=>Genre::all()], 200);
    }

    // GET /api/genres/{id}
    public function show(int $id): JsonResponse
    {
        $genre = Genre::findOrFail($id);
        return response()->json(['status'=>'success','data'=>$genre], 200);
    }

    // GET /api/genres/{id}/books
    public function books(int $id): JsonResponse
    {
        $genre = Genre::findOrFail($id);
        return response()->json(['status'=>'success','data'=>$genre->books], 200);
    }

    // POST /api/genres
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|unique:genres',
        ]);
        $genre = Genre::create($data);
        return response()->json(['status'=>'created','data'=>$genre], 201);
    }

    // PUT/PATCH /api/genres/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $genre = Genre::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string|unique:genres,name,' . $genre->id,
        ]);
        $genre->update($data);
        return response()->json(['status'=>'updated','data'=>$genre], 200);
    }

    // DELETE /api/genres/{id}
    public function destroy(int $id): JsonResponse
    {
        $genre = Genre::findOrFail($id);
        $genre->delete();
        return response()->json(['status'=>'deleted','message'=>'Genre removed'], 204);
    }
}
