<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Check if user exists and is admin
        if (!$user || !$user->role || $user->role->name !== 'admin') {
            return response()->json([
                'message' => 'Forbidden: Admins only.'
            ], 403);
        }

        return $next($request);
    }
}
