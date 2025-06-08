<?php

namespace App\Http\Controllers;

use App\Models\ReadingProgress;
use App\Models\UserBook;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ReadingProgressController extends Controller
{
    /**
     * Display the specified reading progress.
     *
     * @param int $userBookId
     * @return JsonResponse
     */
    public function show(int $userBookId): JsonResponse
    {
        $userBook = UserBook::with(['readingProgress'])
            ->where('user_id', auth()->id())
            ->findOrFail($userBookId);

        // If no progress exists, return default structure
        if (!$userBook->readingProgress) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => null,
                    'current_page' => 0,
                    'user_book_id' => $userBookId,
                    'created_at' => null,
                    'updated_at' => null
                ]
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $userBook->readingProgress
        ]);
    }

    /**
     * Store a newly created reading progress.
     *
     * @param Request $request
     * @param int $userBookId
     * @return JsonResponse
     */
    public function store(Request $request, int $userBookId): JsonResponse
    {
        $userBook = UserBook::where('user_id', auth()->id())
            ->findOrFail($userBookId);

        $data = $request->validate([
            'current_page' => 'required|integer|min:0'
        ]);

        // Check if progress already exists
        if ($userBook->readingProgress) {
            return response()->json([
                'status' => 'error',
                'message' => 'Reading progress already exists for this book'
            ], 409);
        }

        $progress = ReadingProgress::create([
            'user_book_id' => $userBookId,
            'current_page' => $data['current_page']
        ]);

        return response()->json([
            'status' => 'created',
            'data' => $progress
        ], 201);
    }

    /**
     * Update the specified reading progress.
     *
     * @param Request $request
     * @param int $userBookId
     * @return JsonResponse
     */
    public function update(Request $request, int $userBookId): JsonResponse
    {
        $userBook = UserBook::where('user_id', auth()->id())
            ->findOrFail($userBookId);

        $data = $request->validate([
            'current_page' => 'required|integer|min:0',
            'finished' => 'sometimes|boolean',
        ]);

        $progress = ReadingProgress::firstOrCreate(
            ['user_book_id' => $userBookId],
            ['current_page' => $data['current_page']]
        );

        // Update if already exists
        if ($progress->wasRecentlyCreated === false) {
            $progress->update(['current_page' => $data['current_page']]);
        }

        // Update book status if finished
        if ($request->boolean('finished')) {
            $userBook->update(['status' => 'read']);
        }

        return response()->json([
            'status' => 'updated',
            'data' => $progress
        ]);
    }

    /**
     * Remove the specified reading progress.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $progress = ReadingProgress::whereHas('userBook', function($query) {
            $query->where('user_id', Auth::id());
        })->findOrFail($id);

        $progress->delete();

        return response()->json(null, 204);
    }
}
