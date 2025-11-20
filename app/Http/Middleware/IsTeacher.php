<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsTeacher
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role->name !== 'teacher') {
            return response()->json(['message' => 'Forbidden, teacher access only'], 403);
        }

        return $next($request);
    }
}
