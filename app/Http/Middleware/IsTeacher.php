<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsTeacher
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // السماح للأستاذ (2) أو الأدمن (3 أو 1 حسب توزيعك) أو السوبر أدمن
        // ملاحظة: تأكد من أرقام الـ role_id في قاعدة بياناتك (غالباً 1 للأدمن و 2 للأستاذ)
        $isAuthorizedRole = ($user->role_id === 2 || $user->role_id === 1 || $user->is_super_admin);

        if (!$user || !$isAuthorizedRole || $user->status !== 'active') {
            return response()->json([
                'message' => $user && $user->status === 'suspended' 
                    ? 'حسابك معلق، لا يمكنك القيام بهذه العملية' 
                    : 'Forbidden: Teachers and Admins only.'
            ], 403);
        }

        return $next($request);
    }
}