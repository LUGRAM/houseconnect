<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyController extends Controller
{
    /**
     * Liste publique des biens validés (avec filtres)
     */
    public function index(Request $request)
    {
        $query = Property::where('is_validated', true);

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $properties = $query->latest()->paginate(10);

        return response()->json([
            'status' => true,
            'data' => $properties,
        ]);
    }

    /**
     * Détails d’un bien (contrôle d’accès)
     */
    public function show($id)
    {
        /** @var \App\Models\User $auth */
        $auth = Auth::user();
        $property = Property::findOrFail($id);

        if (!$property->is_validated && !$auth->hasRole('admin') && $property->user_id !== Auth::id()) {
            abort(403, 'Accès refusé');
        }

        return response()->json([
            'status' => true,
            'data' => $property,
        ]);
    }

    /**
     * Création d’un nouveau bien (bailleur)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'visit_price' => 'nullable|numeric|min:0',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['is_validated'] = false;

        $property = Property::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Bien soumis pour validation par un administrateur.',
            'data' => $property,
        ], 201);
    }

    /**
     * Mise à jour d’un bien (propriétaire uniquement)
     */
    public function update(Request $request, $id)
    {
        $property = Property::findOrFail($id);

        if ($property->user_id !== Auth::id()) {
            abort(403, 'Accès refusé');
        }

        if ($property->is_validated) {
            abort(403, 'Ce bien est déjà validé et ne peut plus être modifié.');
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'address' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:100',
            'visit_fee' => 'nullable|numeric|min:0',
        ]);

        $property->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Bien mis à jour avec succès.',
            'data' => $property,
        ]);
    }

    /**
     * Suppression d’un bien (propriétaire uniquement)
     */
    public function destroy($id)
    {
        $property = Property::findOrFail($id);

        if ($property->user_id !== Auth::id()) {
            abort(403, 'Accès refusé');
        }

        $property->delete();

        return response()->json([
            'status' => true,
            'message' => 'Bien supprimé avec succès.',
        ]);
    }

    /**
     * Biens d’un utilisateur (accès restreint)
     */
    public function byUser($user_id)
    {
        /** @var \App\Models\User $auth */
        $auth = Auth::user();

        // Autoriser admin ou le propriétaire lui-même
        if ($auth->id != $user_id && ! $auth->hasRole('admin')) {
            abort(403, 'Accès refusé');
        }

        $properties = Property::where('user_id', $user_id)->get();
        /** @var \App\Models\User $auth */
        $auth = Auth::user();

        // Autoriser admin ou le propriétaire lui-même
        if ($auth->id != $user_id && ! $auth->hasRole('admin')) {
            abort(403, 'Accès refusé');
        }

        $properties = Property::where('user_id', $user_id)->get();

        return response()->json([
            'status' => true,
            'data' => $properties,
        ]);
    }
}
