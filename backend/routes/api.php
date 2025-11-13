<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AuthController,
    PropertyController,
    AppointmentController,
    PaymentController,
    ExpenseController,
    DashboardController,
    InvoiceController
};

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// Webhook CinetPay (pas de Sanctum)
Route::post('/payments/cinetpay/webhook', [PaymentController::class, 'webhook'])
    ->withoutMiddleware('auth:sanctum')
    ->name('api.cinetpay.webhook');


/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | AUTH
    |--------------------------------------------------------------------------
    */
    Route::get('/me',              [AuthController::class, 'me']);
    Route::post('/logout',         [AuthController::class, 'logout']);
    Route::put('/update-profile',  [AuthController::class, 'updateProfile']);



    /*
    |--------------------------------------------------------------------------
    | CLIENT — Routes client ONLY
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:client'])->prefix('client')->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'clientSummary']);

        // Appointments
        Route::get('/appointments',           [AppointmentController::class, 'index']);
        Route::post('/appointments',          [AppointmentController::class, 'store']);
        Route::patch('/appointments/{id}/cancel', [AppointmentController::class, 'cancel']);
        
        // Payments
        Route::get('/payments',           [PaymentController::class, 'index']);
        Route::post('/payments/initiate', [PaymentController::class, 'initiate']);
        Route::get('/payments/{id}',      [PaymentController::class, 'show']);

        // Invoices
        Route::get('/invoices',        [InvoiceController::class, 'index']);
        Route::get('/invoices/{id}',   [InvoiceController::class, 'show']);
        Route::post('/invoices/generate/{paymentId}', [InvoiceController::class, 'generateFromPayment']);
    });



    /*
    |--------------------------------------------------------------------------
    | BAILLEUR — Routes bailleur ONLY
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:bailleur'])->prefix('bailleur')->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'landlordSummary']);

        // Properties
        Route::prefix('properties')->group(function () {
            Route::post('/',          [PropertyController::class, 'store']);
            Route::put('/{id}',       [PropertyController::class, 'update']);
            Route::delete('/{id}',    [PropertyController::class, 'destroy']);
            Route::get('/user/{id}',  [PropertyController::class, 'getUserProperties']);
        });

        // Expenses
        Route::apiResource('expenses', ExpenseController::class);

        // Monthly invoices generation
        Route::post('/invoices/generate-monthly', [InvoiceController::class, 'generateMonthly']);
    });



    /*
    |--------------------------------------------------------------------------
    | ADMIN — Routes admin ONLY
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'adminSummary']);

        // Global overdue marking
        Route::post('/invoices/mark-overdue', [InvoiceController::class, 'markOverdue']);
    });
});


/*
|--------------------------------------------------------------------------
| 404 FALLBACK
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return response()->json([
        'status' => false,
        'message' => 'Endpoint API introuvable.'
    ], 404);
});
