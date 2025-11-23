<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsTeacher
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // تحقق من أن المستخدم موجود ودوره teacher
        if (!$user || $user->role !== 'teacher') {
            return response()->json([
                'message' => 'Forbidden: Teachers only.'
            ], 403);
        }

        return $next($request);
    }
}
