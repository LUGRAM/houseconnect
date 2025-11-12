<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AuthController,
    PropertyController,
    AppointmentController,
    PaymentController,
    ExpenseController,
    DashboardController,
    DashboardAdminController,
    InvoiceController
};

/*
|--------------------------------------------------------------------------
| API Routes — HouseConnect
|--------------------------------------------------------------------------
| Organisation :
| - Auth & public
| - Webhooks (externes)
| - Protected routes (auth:sanctum)
|   - Client
|   - Bailleur
|   - Admin
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| ROUTES PUBLIQUES
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/payments/cinetpay/webhook', [PaymentController::class, 'webhook'])
    ->name('api.cinetpay.webhook')
    ->withoutMiddleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| ROUTES PROTÉGÉES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum'])->group(function () {

    /*
    |---------------------------------------------
    | AUTH UTILISATEUR
    |---------------------------------------------
    */
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/update-profile', [AuthController::class, 'updateProfile']);

    /*
    |---------------------------------------------
    | CLIENT — Rendez-vous, Paiements, Tableau
    |---------------------------------------------
    */

    // Rendez-vous accessibles selon le rôle
    Route::middleware(['role:client|bailleur|admin'])->group(function () {
        Route::get('/appointments', [AppointmentController::class, 'index']);            Route::post('/appointments', [AppointmentController::class, 'store']);
        Route::patch('/appointments/{id}/cancel', [AppointmentController::class, 'cancel']);
        Route::get('/appointments/owned', [AppointmentController::class, 'owned']); // vue bailleur
        Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']); // détail
    });


    // Paiements
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index']);            Route::post('/initiate', [PaymentController::class, 'initiate']);
        Route::get('/{id}', [PaymentController::class, 'show']);
    });

    // Tableau client/bailleur unifié
    Route::get('/dashboard', action: [DashboardController::class, 'summary']);
    
    // Factures
    Route::get('invoices', [InvoiceController::class, 'index']);
    Route::get('invoices/{id}', [InvoiceController::class, 'show']);
    Route::post('invoices/generate/{paymentId}', [InvoiceController::class, 'generateFromPayment']);
    Route::post('invoices/generate-monthly', [InvoiceController::class, 'generateMonthly']);
    Route::post('invoices/mark-overdue', [InvoiceController::class, 'markOverdue']);

    /*
    |---------------------------------------------
    | BAILLEUR — Gestion de propriétés et dépenses
    |---------------------------------------------
    */
    Route::middleware('role:bailleur|admin')->group(function () {
        // Propriétés
        Route::prefix('properties')->group(function () {
            Route::post('/', [PropertyController::class, 'store']);
            Route::put('/{id}', [PropertyController::class, 'update']);
            Route::delete('/{id}', [PropertyController::class, 'destroy']);
            Route::get('/user/{id}', [PropertyController::class, 'getUserProperties'])
                ->middleware('can:view-user-properties,id');
        });

        // Dépenses liées aux biens
        Route::apiResource('expenses', ExpenseController::class);
    });

    /*
    |---------------------------------------------
    | ADMIN — Vue globale
    |---------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard', [DashboardAdminController::class, 'summary']);
    });

});

/*
|--------------------------------------------------------------------------
| ROUTE DE SECOURS (404)
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return response()->json([
        'status'  => false,
        'message' => 'Endpoint API introuvable.'
    ], 404);
});

// Dashboard  (pour client ayant un bail)
Route::get('/dashboard', [DashboardController::class, 'clientsummary'])
    ->middleware(['auth:sanctum', 'role:client']);

