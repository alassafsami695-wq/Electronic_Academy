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
    public function withdrawRevenue(Request $request)
    {
        if (!auth()->user()->is_super_admin) {
            return response()->json([
                'success' => false,
                'message' => 'عذراً، يحق فقط للسوبر أدمن تنفيذ عمليات السحب المالي.'
            ], 403);
        }

        // منطق السحب يوضع هنا
        return response()->json([
            'success' => true,
            'message' => 'تم التوجيه لعملية السحب بنجاح.'
        ]);
    }
}