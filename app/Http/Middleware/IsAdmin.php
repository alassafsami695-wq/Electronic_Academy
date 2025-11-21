<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // تحقق من أن المستخدم موجود ودوره admin
        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'message' => 'Forbidden: Admins only.'
            ], 403);
        }

        return $next($request);
    }
}
