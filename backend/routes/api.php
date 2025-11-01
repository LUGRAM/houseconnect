<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes — HouseConnect
|--------------------------------------------------------------------------
| Toutes les routes exposées à l'application mobile Flutter.
| Sécurisées par Laravel Sanctum (token-based).
| Préfixées automatiquement par /api.
|--------------------------------------------------------------------------
*/

// ============================
// AUTHENTIFICATION
// ============================
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/otp/send', [AuthController::class, 'sendOtp']);   // si OTP ajouté plus tard
    Route::post('/otp/verify', [AuthController::class, 'verifyOtp']);
});

// ============================
// ROUTES PROTÉGÉES PAR TOKEN
// ============================
Route::middleware(['auth:sanctum'])->group(function () {

    // Profil utilisateur
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/update-profile', [AuthController::class, 'updateProfile']);

    // ============================
    // PROPRIÉTÉS
    // ============================
    Route::prefix('properties')->group(function () {
        Route::get('/', [PropertyController::class, 'index']);              // liste publique filtrée
        Route::get('/{id}', [PropertyController::class, 'show']);           // détail
        Route::post('/', [PropertyController::class, 'store'])->middleware('role:bailleur'); // création bailleur
        Route::put('/{id}', [PropertyController::class, 'update'])->middleware('role:bailleur');
        Route::delete('/{id}', [PropertyController::class, 'destroy'])->middleware('role:bailleur');
        Route::get('/user/{id}', [PropertyController::class, 'byUser']);    // biens d’un utilisateur
    });

    // ============================
    // RENDEZ-VOUS
    // ============================
    Route::prefix('appointments')->group(function () {
        Route::get('/', [AppointmentController::class, 'index']);
        Route::post('/', [AppointmentController::class, 'store']);
        Route::get('/{id}', [AppointmentController::class, 'show']);
        Route::put('/{id}/cancel', [AppointmentController::class, 'cancel']);
    });

    // ============================
    // PAIEMENTS
    // ============================
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index']);
        Route::post('/initiate', [PaymentController::class, 'initiate']);
        Route::get('/{id}', [PaymentController::class, 'show']);
        Route::post('/webhook', [PaymentController::class, 'webhook'])->withoutMiddleware('auth:sanctum'); // callback public
        Route::post('/cinetpay/webhook', [PaymentController::class, 'webhook'])->name('api.cinetpay.webhook');
    });

    // ============================
    // 🧾 FACTURES (si besoin via PaymentController ou InvoiceController)
    // ============================

    // ============================
    // 💰 DÉPENSES
    // ============================
    Route::prefix('expenses')->group(function () {
        Route::get('/', [ExpenseController::class, 'index']);
        Route::post('/', [ExpenseController::class, 'store']);
        Route::get('/{id}', [ExpenseController::class, 'show']);
        Route::put('/{id}', [ExpenseController::class, 'update']);
        Route::delete('/{id}', [ExpenseController::class, 'destroy']);
    });

    // ============================
    // TABLEAU DE BORD
    // ============================
    Route::get('/dashboard', [DashboardController::class, 'summary']);
});
