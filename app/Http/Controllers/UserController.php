<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Rejestracja użytkownika
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required', 'string', 'min:8', // Minimum 8 znaków
                'regex:/[A-Z]/', // Co najmniej jedna wielka litera
                'regex:/[!@#$%^&*(),.?":{}|<>\\-]/', // Co najmniej jeden znak specjalny
                'confirmed' // Potwierdzenie hasła
            ],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Tworzenie użytkownika
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'data' => $user
        ], 201);
    }

    // Logowanie użytkownika
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Generowanie tokena
        $token = $user->createToken('YourAppName')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ], 200);
    }

    // Pobieranie danych o użytkowniku
    public function me(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => auth()->user()  // Zakładając, że masz middleware dla autoryzacji
        ], 200);
    }

    // Zmiana hasła użytkownika
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => [
                'required', 'string', 'min:8', // Minimum 8 znaków
                'regex:/[A-Z]/', // Co najmniej jedna wielka litera
                'regex:/[!@#$%^&*(),.?":{}|<>\\-]/', // Co najmniej jeden znak specjalny
                'confirmed' // Potwierdzenie hasła
            ],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $user = auth()->user(); // Pobranie aktualnie zalogowanego użytkownika

        // Sprawdzenie poprawności obecnego hasła
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect'
            ], 400);
        }

        // Zmiana hasła
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully'
        ], 200);
    }

    // Wylogowanie użytkownika
    public function logout(): JsonResponse
    {
        // Usunięcie tokenu użytkownika
        auth()->user()->tokens->each(function ($token) {
            $token->delete();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ], 200);
    }
}
