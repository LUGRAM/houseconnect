<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Expense;
use App\Models\Property;

class ExpenseController extends Controller
{
    /**
     * Liste des dépenses du bailleur connecté
     */
    public function index()
    {
        $expenses = Expense::where('user_id', Auth::id())
            ->with('property')
            ->orderByDesc('date')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $expenses,
        ]);
    }

    /**
     * Crée une nouvelle dépense pour un bien appartenant à l’utilisateur
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'category'    => ['required', 'string', Rule::in(['eau', 'electricite', 'entretien', 'autre'])],
            'amount'      => 'required|numeric|min:0',
            'date'        => 'required|date',
            'note'        => 'nullable|string|max:255',
        ]);

        // Vérifie que le bien appartient à l’utilisateur connecté
        $property = Property::findOrFail($validated['property_id']);
        if ($property->user_id !== Auth::id()) {
            abort(403, 'Vous ne pouvez pas ajouter une dépense sur un bien qui ne vous appartient pas.');
        }

        $expense = Expense::create([
            'user_id'     => Auth::id(),
            'property_id' => $property->id,
            'category'    => $validated['category'],
            'amount'      => $validated['amount'],
            'date'        => $validated['date'],
            'note'        => $validated['note'] ?? null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Dépense créée avec succès.',
            'data' => $expense,
        ], 201);
    }

    /**
     * Détail d’une dépense (appartenant à l’utilisateur)
     */
    public function show($id)
    {
        $expense = Expense::where('id', $id)
            ->where('user_id', Auth::id())
            ->with('property')
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data' => $expense,
        ]);
    }

    /**
     * Mise à jour d’une dépense existante
     */
    public function update(Request $request, $id)
    {
        $expense = Expense::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'category' => ['sometimes', 'string', Rule::in(['eau', 'electricite', 'entretien', 'autre'])],
            'amount'   => 'sometimes|numeric|min:0',
            'date'     => 'sometimes|date',
            'note'     => 'nullable|string|max:255',
        ]);

        $expense->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Dépense mise à jour avec succès.',
            'data' => $expense,
        ]);
    }

    /**
     * Suppression d’une dépense
     */
    public function destroy($id)
    {
        $expense = Expense::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $expense->delete();

        return response()->json([
            'status' => true,
            'message' => 'Dépense supprimée avec succès.',
        ]);
    }
}
