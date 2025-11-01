<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Property;
use Illuminate\Support\Facades\Auth;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $query = Property::query()->where('is_validated', true);

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('min_price') && $request->filled('max_price')) {
            $query->whereBetween('price', [$request->min_price, $request->max_price]);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Liste des biens récupérée.',
            'data'    => $query->paginate(10),
        ]);
    }

    public function show($id)
    {
        $property = Property::findOrFail($id);

        return response()->json([
            'status'  => true,
            'message' => 'Détails du bien.',
            'data'    => $property,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'price'       => 'required|numeric|min:0',
            'address'     => 'required|string|max:255',
            'city'        => 'required|string|max:100',
            'visit_price' => 'nullable|numeric|min:0',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['is_validated'] = false;

        $property = Property::create($validated);

        return response()->json([
            'status'  => true,
            'message' => 'Bien soumis pour validation.',
            'data'    => $property,
        ]);
    }

    public function update(Request $request, $id)
    {
        $property = Property::findOrFail($id);

        if ($property->user_id !== Auth::id()) {
            return response()->json(['status' => false, 'message' => 'Accès refusé.'], 403);
        }

        if ($property->is_validated) {
            return response()->json(['status' => false, 'message' => 'Bien déjà validé. Modification refusée.'], 403);
        }

        $validated = $request->validate([
            'title'       => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'nullable|numeric|min:0',
            'address'     => 'nullable|string|max:255',
            'city'        => 'nullable|string|max:100',
            'visit_price' => 'nullable|numeric|min:0',
        ]);

        $property->update($validated);

        return response()->json([
            'status'  => true,
            'message' => 'Bien mis à jour.',
            'data'    => $property,
        ]);
    }

    public function destroy($id)
    {
        $property = Property::findOrFail($id);

        if ($property->user_id !== Auth::id()) {
            return response()->json(['status' => false, 'message' => 'Accès refusé.'], 403);
        }

        $property->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Bien supprimé.',
        ]);
    }

    public function byUser($id)
    {
        $properties = Property::where('user_id', $id)->get();

        return response()->json([
            'status'  => true,
            'message' => 'Biens de l’utilisateur récupérés.',
            'data'    => $properties,
        ]);
    }
}
