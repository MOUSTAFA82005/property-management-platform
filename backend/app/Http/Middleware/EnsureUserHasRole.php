<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate a route group to one of the platform's two roles.
 *
 * This enforces role separation only — it says nothing about which records a
 * user may touch. Per-record ownership stays with the policies and the query
 * scoping in each controller.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! in_array($user->role, $roles, true)) {
            return response()->json([
                'message' => 'This action is unauthorized.',
            ], 403);
        }

        return $next($request);
    }
}
