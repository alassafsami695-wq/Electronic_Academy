<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsTeacher
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // السماح للـ Teacher أو Admin أو Super Admin
        if (
            !$user ||
            (
                $user->role_id !== 2 && // Teacher
                $user->role_id !== 3 && // Admin
                !$user->is_super_admin   // Super Admin
            )
        ) {
            return response()->json([
                'message' => 'Forbidden: Teachers only.'
            ], 403);
        }

        return $next($request);
    }
}
