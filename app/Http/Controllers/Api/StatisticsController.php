<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function getStudentsStats()
{
    // 1. التأكد من جلب بيانات المستخدم مع دوره فوراً لتجنب الـ null
    $user = User::with('role')->find(auth()->id());
    
    if (!$user) {
        return response()->json(['status' => false, 'message' => 'المستخدم غير موجود'], 401);
    }

    $query = User::query();

    // 2. التحقق من الأدمن (باستخدام الاسم أو الحقل المباشر)
    if ($user->is_super_admin || ($user->role && $user->role->name === 'admin')) {
        $students = $query->whereHas('role', function($q) {
            $q->where('name', 'user');
        })->withCount('enrolledCourses') // تأكد من اسم العلاقة هنا
        ->get();

        $message = 'تم جلب جميع طلاب المنصة بنجاح (عرض كأدمن)';
    } 
    
    // 3. التحقق من الأستاذ
    elseif ($user->role && $user->role->name === 'teacher') {
        $teacherId = $user->id;

        // جلب الطلاب المشتركين في كورسات هذا الأستاذ فقط
        $students = $query->whereHas('enrolledCourses', function($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        })
        ->withCount(['enrolledCourses' => function($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        }])
        ->get();

        $message = 'تم جلب الطلاب المشتركين في كورساتك بنجاح';
    } 
    else {
        return response()->json(['status' => false, 'message' => 'غير مصرح لك بالوصول'], 403);
    }

    return response()->json([
        'status' => true,
        'message' => $message,
        'count' => $students->count(),
        'data' => $students
    ]);
}
}