<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'nullable|email|unique:users,email',
            'phone'    => 'required|string|unique:users,phone',
            'password' => 'required|min:6',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);
        $user->assignRole('client');

        return response()->json([
            'status'  => true,
            'message' => 'Inscription réussie',
            'data'    => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'nullable|email',
            'phone'    => 'nullable|string',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'] ?? null)
            ->orWhere('phone', $validated['phone'] ?? null)
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'credentials' => ['Identifiants invalides.'],
            ]);
        }

        $token = $user->createToken('houseconnect_token')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Connexion réussie',
            'token'   => $token,
            'user'    => $user,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'status' => true,
            'data'   => $request->user(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'  => 'nullable|string|max:100',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|unique:users,phone,' . $user->id,
        ]);

        $user->update($validated);

        return response()->json([
            'status'  => true,
            'message' => 'Profil mis à jour avec succès.',
            'data'    => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Déconnexion réussie.',
        ]);
    }
}
