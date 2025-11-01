<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\DeviceToken;

class SyncDeviceToken
{
    /**
     * Intercepte chaque requête authentifiée et synchronise le token FCM/OneSignal
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // ✅ Si l'utilisateur est connecté
        if (Auth::check()) {
            $deviceToken = $request->header('X-Device-Token');

            if ($deviceToken) {
                DeviceToken::updateOrCreate(
                    [
                        'user_id' => Auth::id(),
                        'token'   => $deviceToken,
                    ],
                    [
                        'last_used_at' => now(),
                        'platform'     => $request->header('X-Device-Platform', 'unknown'),
                        'app_version'  => $request->header('X-App-Version', 'unknown'),
                    ]
                );

                Log::info('Device token synchronized', [
                    'user_id' => Auth::id(),
                    'token'   => substr($deviceToken, 0, 10) . '...',
                ]);
            }
        }

        return $response;
    }
}
