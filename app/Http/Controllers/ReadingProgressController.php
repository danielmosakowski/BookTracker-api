<?php

namespace App\Http\Controllers;

use App\Models\ReadingProgress;
use App\Models\UserBook;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReadingProgressController extends Controller
{
    // GET /api/user-books/{collectionId}/progress
    public function show(int $collectionId): JsonResponse
    {
        $collection = UserBook::findOrFail($collectionId);
        return response()->json(['status'=>'success','data'=>$collection->readingProgress], 200);
    }

    // POST/PATCH /api/user-books/{collectionId}/progress
    public function update(Request $request, int $collectionId): JsonResponse
    {
        $collection = UserBook::findOrFail($collectionId);

        $data = $request->validate([
            'current_page' => 'required|integer|min:0',
            'finished'     => 'sometimes|boolean',
        ]);

        $progress = ReadingProgress::updateOrCreate(
            ['user_books_collection_id' => $collection->id],
            $data
        );

        return response()->json(['status'=>'updated','data'=>$progress], 200);
    }

    // DELETE /api/progress/{id}
    public function destroy(int $id): JsonResponse
    {
        $progress = ReadingProgress::findOrFail($id);
        $progress->delete();
        return response()->json(['status'=>'deleted','message'=>'Removed'], 204);
    }
}
