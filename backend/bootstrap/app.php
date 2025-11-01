<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\{SyncDeviceToken, RoleMiddleware};

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Middleware globaux (disponibles dans toutes les routes)
        $middleware->api(prepend: [
            SyncDeviceToken::class,
        ]);

        // Alias utilisables dans les routes
        $middleware->alias([
            'role' => RoleMiddleware::class,      // Vérifie les rôles
            'auth' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Personnalisation globale des erreurs (facultatif)
        $exceptions->render(function (Throwable $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 
            $status = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException
                ? $e->getStatusCode()
                : ($e->getCode() >= 100 && $e->getCode() < 600 ? $e->getCode() : 500));
            }
        });
    })
    ->create();
