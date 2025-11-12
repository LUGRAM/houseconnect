<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

// --- Gestion du rôle actif ---
Route::get('/switch-role/{role}', function ($role) {
    if (in_array($role, ['client', 'bailleur', 'admin'])) {
        session(['active_role' => $role]);
        return redirect()->back()->with('success', "Mode $role activé.");
    }
    abort(403, 'Rôle invalide');
})->middleware('auth');

// --- Dashboards Web ---
Route::middleware(['auth', 'role:client'])->get('/client/dashboard', fn() => view('dashboards.client'));
Route::middleware(['auth', 'role:bailleur'])->get('/bailleur/dashboard', fn() => view('dashboards.bailleur'));
Route::middleware(['auth', 'role:admin'])->get('/admin/dashboard', fn() => view('dashboards.admin'));
