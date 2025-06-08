<?php

namespace App\Http\Controllers;

use App\Models\UserChallenge;
use App\Models\Challenge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserChallengeController extends Controller
{
    /**
     * Lista wyzwań użytkownika
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $challenges = $user->challenges()
            ->withPivot(['completed_books', 'is_completed'])
            ->get();

        return response()->json([
            'data' => $challenges->map(function ($challenge) {
                return [
                    'id' => $challenge->id,
                    'name' => $challenge->name,
                    'description' => $challenge->description,
                    'target_books' => $challenge->target_books,
                    'start_date' => $challenge->start_date,
                    'end_date' => $challenge->end_date,
                    'progress' => [
                        'completed_books' => $challenge->pivot->completed_books,
                        'is_completed' => $challenge->pivot->is_completed,
                        'remaining_books' => max(0, $challenge->target_books - $challenge->pivot->completed_books),
                    ],
                ];
            })
        ]);
    }

    /**
     * Dołączanie do wyzwania
     */
    public function store(Request $request, Challenge $challenge): JsonResponse
    {
        $user = Auth::user();

        if ($user->challenges()->where('challenges.id', $challenge->id)->exists()) {
            return response()->json([
                'message' => 'You are already participating in this challenge'
            ], 409);
        }

        $user->challenges()->attach($challenge->id, [
            'completed_books' => 0,
            'is_completed' => false
        ]);

        return response()->json([
            'message' => 'Successfully joined the challenge',
            'data' => [
                'challenge_id' => $challenge->id,
                'progress' => 0
            ]
        ], 201);
    }

    /**
     * Aktualizacja postępu w wyzwaniu
     */
    public function update(Request $request, Challenge $challenge): JsonResponse
    {
        $request->validate([
            'completed_books' => 'required|integer|min:0'
        ]);

        $user = Auth::user();
        $userChallenge = $user->challenges()
            ->where('challenges.id', $challenge->id)
            ->first();

        if (!$userChallenge) {
            return response()->json([
                'message' => 'You are not participating in this challenge'
            ], 404);
        }

        $completedBooks = $request->completed_books;
        $isCompleted = $completedBooks >= $challenge->target_books;

        $user->challenges()->updateExistingPivot($challenge->id, [
            'completed_books' => $completedBooks,
            'is_completed' => $isCompleted
        ]);

        return response()->json([
            'message' => 'Progress updated successfully',
            'data' => [
                'challenge_id' => $challenge->id,
                'completed_books' => $completedBooks,
                'is_completed' => $isCompleted,
                'remaining_books' => max(0, $challenge->target_books - $completedBooks)
            ]
        ]);
    }

    /**
     * Szczegóły uczestnictwa w konkretnym wyzwaniu
     */
    public function show(Challenge $challenge): JsonResponse
    {
        $user = Auth::user();
        $participation = $user->challenges()
            ->where('challenges.id', $challenge->id)
            ->first();

        if (!$participation) {
            return response()->json([
                'message' => 'You are not participating in this challenge'
            ], 404);
        }

        return response()->json([
            'data' => [
                'challenge' => $participation->only(['id', 'name', 'description', 'target_books']),
                'progress' => [
                    'completed_books' => $participation->pivot->completed_books,
                    'is_completed' => $participation->pivot->is_completed,
                    'remaining_books' => max(0, $participation->target_books - $participation->pivot->completed_books),
                ]
            ]
        ]);
    }

    /**
     * Rezygnacja z wyzwania
     */
    public function destroy(Challenge $challenge): JsonResponse
    {
        $user = Auth::user();

        if (!$user->challenges()->where('challenges.id', $challenge->id)->exists()) {
            return response()->json([
                'message' => 'You are not participating in this challenge'
            ], 404);
        }

        $user->challenges()->detach($challenge->id);

        return response()->json([
            'message' => 'Successfully left the challenge'
        ], 204);
    }

    /**
     * Aktualizacja postępu po przeczytaniu książki
     */
    public function incrementProgress(Challenge $challenge): JsonResponse
    {
        $user = Auth::user();
        $userChallenge = $user->challenges()
            ->where('challenges.id', $challenge->id)
            ->first();

        if (!$userChallenge) {
            return response()->json([
                'message' => 'You are not participating in this challenge'
            ], 404);
        }

        $newCount = $userChallenge->pivot->completed_books + 1;
        $isCompleted = $newCount >= $challenge->target_books;

        $user->challenges()->updateExistingPivot($challenge->id, [
            'completed_books' => $newCount,
            'is_completed' => $isCompleted
        ]);

        return response()->json([
            'message' => 'Progress incremented successfully',
            'data' => [
                'completed_books' => $newCount,
                'is_completed' => $isCompleted
            ]
        ]);
    }
}
