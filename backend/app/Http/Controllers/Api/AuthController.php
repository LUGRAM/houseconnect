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
            'password' => 'required|string|min:6|confirmed',
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
            'email'    => 'nullable|email|required_without:phone',
            'phone'    => 'nullable|string|required_without:email',
            'password' => 'required|string|min:6',
        ]);

        if (!empty($validated['email'])) {
            $user = User::where('email', $validated['email'])->first();
        } elseif (!empty($validated['phone'])) {
            $user = User::where('phone', $validated['phone'])->first();
        } else {
            return response()->json(['message' => 'Email ou téléphone requis.'], 422);
        }

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
            'email' => 'nullable|email|required_without:phone|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|required_without:email|unique:users,phone,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

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

    public function sendOtp(Request $request)
    {
        return response()->json([
            'status'  => false,
            'message' => 'Fonctionnalité OTP non encore disponible',
        ], 501);
    }

    public function verifyOtp(Request $request)
    {
        return response()->json([
            'status'  => false,
            'message' => 'Fonctionnalité OTP non encore disponible',
        ], 501);
    }
}
