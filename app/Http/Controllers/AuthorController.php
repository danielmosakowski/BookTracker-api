<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthorController extends Controller
{
    // GET /api/authors
    public function index(): JsonResponse
    {
        return response()->json(['status'=>'success','data'=>Author::all()], 200);
    }

    // GET /api/authors/{id}
    public function show(int $id): JsonResponse
    {
        $author = Author::findOrFail($id);
        return response()->json(['status'=>'success','data'=>$author], 200);
    }

    // GET /api/authors/{id}/books
    public function books(int $id): JsonResponse
    {
        $author = Author::findOrFail($id);
        return response()->json(['status'=>'success','data'=>$author->books], 200);
    }

    // POST /api/authors
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'biography' => 'nullable|string',
        ]);
        $author = Author::create($data);
        return response()->json(['status'=>'created','data'=>$author], 201);
    }

    // PUT/PATCH /api/authors/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $author = Author::findOrFail($id);
        $data = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'biography' => 'nullable|string',
        ]);
        $author->update($data);
        return response()->json(['status'=>'updated','data'=>$author], 200);
    }

    // DELETE /api/authors/{id}
    public function destroy(int $id): JsonResponse
    {
        $author = Author::findOrFail($id);
        $author->delete();
        return response()->json(['status'=>'deleted','message'=>'Author removed'], 204);
    }
}
