<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Http\Resources\PublicCourseResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * 1. عرض الكورسات المشتراة للطالب (بدون دروس)
     * تظهر هنا الكورسات التي دفع الطالب ثمنها فقط مع نسبة التقدم
     */
    public function myCourses()
    {
        $user = auth()->user();

        // الـ progress_percentage سيظهر تلقائياً مع كل كورس بفضل ميزة appends في الموديل
        $courses = $user->enrolledCourses()
            ->with(['teacher', 'path']) 
            ->get();

        return response()->json([
            'status' => 'success',
            'courses' => $courses
        ]);
    }

    /**
     * 2. عرض تفاصيل كورس محدد
     * تظهر الدروس هنا فقط إذا كان المستخدم (أدمن / مدرس الكورس / طالب مشترك)
     */
    public function show(Course $course)
    {
        $user = auth()->user();

        // التحقق من الصلاحيات: أدمن أو صاحب الكورس أو طالب مشترك
        $isOwnerOrAdmin = $user->isAdmin() || $course->teacher_id === $user->id;
        $isSubscribed = $user->enrolledCourses()->where('course_id', $course->id)->exists();

        if ($isOwnerOrAdmin || $isSubscribed) {
            // تحميل الدروس والعلاقات عند الدخول لصفحة الكورس فقط
            $course->load(['teacher', 'path', 'lessons']);
            return new CourseResource($course);
        }

        return response()->json([
            'message' => 'هذا المحتوى محمي. يرجى الاشتراك في الكورس أولاً لتتمكن من رؤية الدروس.'
        ], 403);
    }

    /**
     * 3. عرض جميع الكورسات (للمدرسين في لوحتهم أو البحث العام)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Course::with(['teacher', 'path']);

        // دعم البحث بالاسم
        if ($request->has('search') && !empty($request->search)) {
            $query->where('title', 'LIKE', '%' . $request->search . '%');
        }

        // إذا كان مدرس، يرى كورساته فقط إلا إذا طلب البحث العام
        if ($user && $user->isTeacher() && !$request->has('public')) {
            $query->where('teacher_id', $user->id);
        }

        $courses = $query->latest()->paginate(10);
        return CourseResource::collection($courses);
    }

    /**
     * 4. إنشاء كورس جديد
     */
    public function store(StoreCourseRequest $request)
    {
        $data = $request->validated();
        $data['teacher_id'] = auth()->id();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('courses', 'public');
        }

        $course = Course::create($data);
        return new CourseResource($course);
    }

    /**
     * 5. تحديث بيانات الكورس
     */
    public function update(UpdateCourseRequest $request, Course $course)
    {
        if ($course->teacher_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['message' => 'غير مصرح لك بتعديل هذا الكورس'], 403);
        }

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            if ($course->photo) {
                Storage::disk('public')->delete($course->photo);
            }
            $data['photo'] = $request->file('photo')->store('courses', 'public');
        }

        $course->update($data);
        return new CourseResource($course);
    }

    /**
     * 6. حذف الكورس
     */
    public function destroy(Course $course)
    {
        if ($course->teacher_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['message' => 'غير مصرح لك بحذف هذا الكورس'], 403);
        }

        if ($course->photo) {
            Storage::disk('public')->delete($course->photo);
        }

        $course->delete();
        return response()->json(['message' => 'تم حذف الكورس بنجاح']);
    }

    /**
     * 7. العرض العام (قبل الشراء)
     */
    public function publicShow(Course $course)
    {
        $course->load(['teacher', 'path']);
        return new PublicCourseResource($course);
    }

    
     // الكورسات الأكثر مبيعاً
     
    public function bestSelling()
    {
        return response()->json(
            Course::orderByDesc('sales_count')->take(5)->get()
        );
    }
    public function getCoursesWithExams()
    {
        // جلب كورسات المدرس الحالي مع علاقة الأسئلة (الاختبارات)
        $courses = Course::where('teacher_id', auth()->id())
            ->with('questions') 
            ->get();

        return response()->json(['status' => true, 'data' => $courses]);
    }

    public function getDetailedCourses()
    {
        // جلب كورسات المدرس مع الدروس والتعليقات وصاحب كل تعليق
        $courses = Course::where('teacher_id', auth()->id())
            ->with(['lessons.comments.user']) 
            ->get();

        return response()->json(['status' => true, 'data' => $courses]);
    }
}