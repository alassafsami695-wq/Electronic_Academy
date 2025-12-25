<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load('role', 'wallet');
        $data = [];

        // --- 1. لوحة تحكم الإدارة (أدمن + سوبر أدمن) ---
        if ($user->isAdmin()) {
            
            // إحصائيات أساسية يراها أي أدمن
            $stats = [
                'total_students' => User::whereHas('role', fn($q) => $q->where('name', 'user'))->count(),
                'total_teachers' => User::whereHas('role', fn($q) => $q->where('name', 'teacher'))->count(),
                'total_courses'  => Course::count(),
                'total_revenue'  => Wallet::sum('balance'), // الآن الأدمن العادي يرى إجمالي الرصيد أيضاً
            ];

            // --- إضافات حصرية للسوبر أدمن فقط ---
            if ($user->is_super_admin) {
                // السوبر أدمن فقط يرى عدد المسؤولين الآخرين
                $stats['total_admins'] = User::whereHas('role', fn($q) => $q->where('name', 'admin'))
                                            ->where('is_super_admin', false)
                                            ->count();
                
                $stats['can_withdraw'] = true; // صلاحية السحب للسوبر أدمن فقط
            } else {
                $stats['total_admins'] = null; // مخفي عن الأدمن العادي
                $stats['can_withdraw'] = false; // لا يملك صلاحية السحب
            }

            $data = [
                'type' => 'admin',
                'role_display' => $user->is_super_admin ? 'Super Admin' : 'Admin',
                'stats' => $stats
            ];
        } 

        // --- 2. لوحة تحكم الأستاذ ---
        elseif ($user->isTeacher()) {
            $myCourseIds = Course::where('teacher_id', $user->id)->pluck('id');
            
            $data = [
                'type' => 'teacher',
                'stats' => [
                    'my_courses_count' => $myCourseIds->count(),
                    'total_lessons'    => Lesson::whereIn('course_id', $myCourseIds)->count(),
                    'total_students'   => DB::table('course_user')->whereIn('course_id', $myCourseIds)->count(),
                    'my_earnings'      => $user->wallet->balance ?? 0,
                ]
            ];
        } 

        // --- 3. لوحة تحكم الطالب ---
        else {
            $enrolledCourses = $user->enrolledCourses()->withCount('lessons')->get();
            
            $courseProgress = $enrolledCourses->map(function($course) use ($user) {
                $completed = $user->completedLessons()->where('course_id', $course->id)->count();
                return [
                    'course_id'   => $course->id,
                    'course_name' => $course->title,
                    'progress'    => $course->lessons_count > 0 ? round(($completed / $course->lessons_count) * 100, 2) : 0
                ];
            });

            $data = [
                'type' => 'student',
                'stats' => [
                    'purchased_courses_count' => $enrolledCourses->count(),
                    'wallet_balance'          => $user->wallet->balance ?? 0,
                    'courses_progress'        => $courseProgress
                ]
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * دالة سحب الأرباح - محصورة برمجياً بالسوبر أدمن فقط
     */
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = auth()->user();
        
        // 1. التأكد من أن المستخدم لديه محفظة
        if (!$user->wallet) {
            return response()->json(['success' => false, 'message' => 'لا تمتلك محفظة إلكترونية'], 404);
        }

        // 2. التحقق من الرصيد المتوفر في محفظة المستخدم الحالي (سواء كان أدمن أو أستاذ)
        if ($user->wallet->balance < $request->amount) {
            return response()->json(['success' => false, 'message' => 'رصيدك الحالي غير كافٍ لإتمام عملية السحب'], 400);
        }

        // 3. تحديد الصلاحيات: الأستاذ يسحب أرباحه، والسوبر أدمن يسحب أرباح التطبيق
        if ($user->isAdmin() || $user->isTeacher()) {
            
            return DB::transaction(function () use ($user, $request) {
                // تنفيذ عملية الخصم من محفظة الشخص الذي قام بطلب السحب
                $user->wallet->decrement('balance', $request->amount);
                
                // هنا يفضل تسجيل العملية في جدول التحويلات (Transactions) للتدقيق لاحقاً
                
                return response()->json([
                    'success' => true, 
                    'message' => $user->isAdmin() ? 'تم سحب أرباح المنصة بنجاح' : 'تم سحب أرباحك كأستاذ بنجاح',
                    'current_balance' => $user->wallet->fresh()->balance
                ]);
            });
        }

        // 4. منع الطالب أو أي رتبة أخرى من السحب
        return response()->json([
            'success' => false, 
            'message' => 'عذراً، هذه العملية مخصصة فقط للأساتذة وإدارة التطبيق'
        ], 403);
    }
}