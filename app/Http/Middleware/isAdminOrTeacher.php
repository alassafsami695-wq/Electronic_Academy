<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdminOrTeacher
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // السماح للأدمن (1) أو الأستاذ (2) بشرط النشاط
        if (!$user || !in_array($user->role_id, [1, 2]) || $user->status !== 'active') {
            return response()->json([
                'message' => $user && $user->status === 'suspended' 
                    ? 'حسابك معلق، يرجى التواصل مع الإدارة' 
                    : 'Unauthorized'
            ], 403);
        }

        return $next($request);
    }
}