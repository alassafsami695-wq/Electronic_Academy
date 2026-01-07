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
            $stats = [
                'total_students' => User::whereHas('role', fn($q) => $q->where('name', 'user'))->count(),
                'total_teachers' => User::whereHas('role', fn($q) => $q->where('name', 'teacher'))->count(),
                'total_courses'  => Course::count(),
                'total_revenue'  => Wallet::sum('balance'),
            ];

            if ($user->is_super_admin) {
                $stats['total_admins'] = User::whereHas('role', fn($q) => $q->where('name', 'admin'))
                                            ->where('is_super_admin', false)
                                            ->count();
                $stats['can_withdraw'] = true;
            } else {
                $stats['total_admins'] = null;
                $stats['can_withdraw'] = false;
            }

            $data = [
                'type' => 'admin',
                'role_display' => $user->is_super_admin ? 'Super Admin' : 'Admin',
                'stats' => $stats
            ];
        } 

        // --- 2. لوحة تحكم الأستاذ (المحدثة) ---
        elseif ($user->isTeacher()) {
            // جلب معرفات كورسات الأستاذ
            $myCourseIds = Course::where('teacher_id', $user->id)->pluck('id');
            
            // 1. حساب عدد الطلاب الفريدين (بدون تكرار)
            $uniqueStudentsCount = DB::table('course_user')
                ->whereIn('course_id', $myCourseIds)
                ->distinct('user_id')
                ->count();

            // 2. جلب قائمة الطلاب مع عدد الكورسات التي اشتركوا بها لدى هذا الأستاذ
            $studentsList = User::whereHas('enrolledCourses', function($q) use ($myCourseIds) {
                    $q->whereIn('courses.id', $myCourseIds);
                })
                ->withCount(['enrolledCourses' => function($q) use ($myCourseIds) {
                    $q->whereIn('courses.id', $myCourseIds);
                }])
                ->get(['id', 'name', 'email'])
                ->map(function($student) {
                    return [
                        'student_name' => $student->name,
                        'student_email' => $student->email,
                        'courses_bought_count' => $student->enrolled_courses_count
                    ];
                });

            $data = [
                'type' => 'teacher',
                'stats' => [
                    'my_courses_count' => $myCourseIds->count(),
                    'total_lessons'    => Lesson::whereIn('course_id', $myCourseIds)->count(),
                    'total_unique_students' => $uniqueStudentsCount,
                    'my_earnings'      => $user->wallet->balance ?? 0,
                ],
                'students_details' => $studentsList // القائمة التفصيلية التي طلبتها
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

    public function withdraw(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:1']);
        $user = auth()->user();
        
        if (!$user->wallet) {
            return response()->json(['success' => false, 'message' => 'لا تمتلك محفظة إلكترونية'], 404);
        }

        // الرسالة المعدلة (حل خطأ JsonFragment في اختبار withdraw)
        if ($user->wallet->balance < $request->amount) {
            return response()->json([
                'success' => false, 
                'message' => 'رصيدك الحالي غير كافٍ لإتمام عملية السحب'
            ], 400);
        }

        if ($user->isAdmin() || $user->isTeacher()) {
            return DB::transaction(function () use ($user, $request) {
                $user->wallet->decrement('balance', $request->amount);
                return response()->json([
                    'success' => true, 
                    'message' => 'تم سحب الأرباح بنجاح',
                    'current_balance' => $user->wallet->fresh()->balance
                ]);
            });
        }

        return response()->json(['success' => false, 'message' => 'غير مصرح لك'], 403);
    }
}