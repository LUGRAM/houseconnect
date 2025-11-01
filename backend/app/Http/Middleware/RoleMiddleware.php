<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    /**
     * Vérifie si l’utilisateur a un rôle autorisé.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentification requise',
            ], 401);
        }

        // Vérifie si l'utilisateur a au moins un des rôles requis
        if (! $user->hasAnyRole($roles)) {
            Log::warning('Tentative d’accès non autorisée', [
                'user_id' => $user->id,
                'roles_required' => $roles,
                'user_roles' => $user->getRoleNames(),
                'path' => $request->path(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Accès refusé. Rôle requis : ' . implode(', ', $roles),
            ], 403);
        }

        return $next($request);
    }
}
