<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChallengeController extends Controller
{
    public function index(): JsonResponse
    {
        $challenges = Challenge::all();
        return response()->json(['data' => $challenges]);
    }

    public function show(Challenge $challenge): JsonResponse
    {
        return response()->json(['data' => $challenge]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'target_books' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $challenge = Challenge::create($validator->validated());

        return response()->json([
            'message' => 'Challenge created successfully',
            'data' => $challenge
        ], 201);
    }

    public function update(Request $request, Challenge $challenge): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'target_books' => 'sometimes|integer|min:1',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $challenge->update($validator->validated());

        return response()->json([
            'message' => 'Challenge updated successfully',
            'data' => $challenge
        ]);
    }

    public function destroy(Challenge $challenge): JsonResponse
    {
        $challenge->delete();
        return response()->json(['message' => 'Challenge deleted successfully'], 204);
    }

    public function joinChallenge(Challenge $challenge): JsonResponse
    {
        $user = Auth::user();

        if ($user->challenges()->where('challenge_id', $challenge->id)->exists()) {
            return response()->json(['message' => 'You have already joined this challenge'], 400);
        }

        $user->challenges()->attach($challenge->id, [
            'completed_books' => 0,
            'is_completed' => false
        ]);

        return response()->json(['message' => 'Successfully joined the challenge'], 200);
    }

    public function userProgress(Challenge $challenge): JsonResponse
    {
        $user = Auth::user();
        $progress = $user->challenges()->where('challenge_id', $challenge->id)->first();

        if (!$progress) {
            return response()->json(['message' => 'You are not participating in this challenge'], 404);
        }

        return response()->json(['data' => $progress->pivot]);
    }
}
