<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // السماح فقط للأدمن أو السوبر أدمن بشرط أن يكون الحساب نشطاً
        if (!$user || ($user->role_id !== 1 && !$user->is_super_admin) || $user->status !== 'active') {
            return response()->json([
                'message' => $user && $user->status === 'suspended' 
                    ? 'حسابك معلق، يرجى التواصل مع الإدارة' 
                    : 'Unauthorized'
            ], 403);
        }

        return $next($request);
    }
}