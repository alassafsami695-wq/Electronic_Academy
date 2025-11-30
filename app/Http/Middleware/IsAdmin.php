<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdmin
{
    
    //-------------------------------التحقق من أن المستخدم الحالي هو أدمن أو سوبر أدمن------------------------

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || ($user->role_id !== 1 && !$user->is_super_admin)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
