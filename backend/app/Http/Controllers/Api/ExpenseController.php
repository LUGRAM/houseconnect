<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::where('user_id', Auth::id())
            ->with(['property'])
            ->latest()
            ->get();

        return response()->json([
            'status'  => true,
            'message' => 'Liste des dépenses récupérée.',
            'data'    => $expenses,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'category'    => 'required|string|max:50',
            'amount'      => 'required|numeric|min:0',
            'date'        => 'required|date',
            'note'        => 'nullable|string|max:255',
        ]);

        $validated['user_id'] = Auth::id();
        $expense = Expense::create($validated);

        return response()->json([
            'status'  => true,
            'message' => 'Dépense enregistrée avec succès.',
            'data'    => $expense,
        ]);
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'category' => 'nullable|string|max:50',
            'amount'   => 'nullable|numeric|min:0',
            'date'     => 'nullable|date',
            'note'     => 'nullable|string|max:255',
        ]);

        $expense->update($validated);

        return response()->json([
            'status'  => true,
            'message' => 'Dépense mise à jour avec succès.',
            'data'    => $expense,
        ]);
    }

    public function destroy($id)
    {
        $expense = Expense::where('user_id', Auth::id())->findOrFail($id);
        $expense->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Dépense supprimée avec succès.',
        ]);
    }

    public function show($id)
    {
        $expense = Expense::where('user_id', Auth::id())
            ->with('property')
            ->findOrFail($id);

        return response()->json([
            'status'  => true,
            'message' => 'Détails de la dépense récupérés.',
            'data'    => $expense,
        ]);
    }
}
