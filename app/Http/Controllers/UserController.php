<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    // GET /api/users
    public function index(): JsonResponse
    {
        $users = User::all();
        return response()->json(['status' => 'success', 'data' => $users], 200);
    }

    // GET /api/users/{id}
    public function show(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        return response()->json(['status' => 'success', 'data' => $user], 200);
    }

    // POST /api/users
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => [
                'required', 'string', 'min:8',
                'regex:/[A-Z]/',
                'regex:/[!@#$%^&*(),.?":{}|<>\\-]/',
                'confirmed'
            ],
            'is_admin' => 'sometimes|boolean',
        ]);

        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);

        return response()->json(['status' => 'created', 'data' => $user], 201);
    }

    // PUT/PATCH /api/users/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => [
                'nullable', 'string', 'min:8',
                'regex:/[A-Z]/',
                'regex:/[!@#$%^&*(),.?":{}|<>\\-]/',
                'confirmed'
            ],
            'is_admin' => 'sometimes|boolean',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        return response()->json(['status' => 'updated', 'data' => $user], 200);
    }

    // DELETE /api/users/{id}
    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['status' => 'deleted', 'message' => 'User removed'], 204);
    }
}

