<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Http\Resources\PublicCourseResource;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    // ----------------------------- عرض جميع الكورسات (حسب الدور) -----------------------------
    public function index()
    {
        $user = auth()->user();

        // تحميل اشتراكات المستخدم قبل إرسال الكورسات
        if ($user) {
            $user->load('enrolledCourses');
        }

        if ($user->role === 'Admin') {
            $courses = Course::with(['teacher', 'path'])->paginate(10);
            return CourseResource::collection($courses);
        }

        if ($user->role === 'Teacher') {
            $courses = Course::with(['teacher', 'path'])
                            ->where('teacher_id', $user->id)
                            ->paginate(10);

            return CourseResource::collection($courses);
        }

        return response()->json(['message' => 'غير مصرح لك'], 403);
    }


    // ----------------------------- إنشاء كورس جديد (للمدرّس) -----------------------------
    public function store(StoreCourseRequest $request)
    {
        $user = auth()->user();

        // فقط المدرّس يمكنه إنشاء كورس من هذه الروت
        if ($user->role !== 'Teacher') {
            return response()->json(['message' => 'غير مصرح لك بإنشاء كورس'], 403);
        }

        $data = $request->validated();

        // ربط الكورس بالمدرّس
        $data['teacher_id'] = $user->id;

        // رفع الصورة إن وجدت
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('courses', 'public');
        }

        $course = Course::create($data);

        return new CourseResource($course);
    }

    // ----------------------------- عرض كورس واحد (مدرّس/أدمن) -----------------------------
    public function show(Course $course)
    {
        $user = auth()->user();

        if ($user) {
            $user->load('enrolledCourses');
        }

        if ($user->role === 'Teacher' && $course->teacher_id !== $user->id) {
            return response()->json(['message' => 'غير مصرح لك'], 403);
        }

        if ($user->role !== 'Teacher' && $user->role !== 'Admin') {
            return response()->json(['message' => 'غير مصرح لك'], 403);
        }

        $course->load(['teacher', 'path', 'lessons']);

        return new CourseResource($course);
    }

    // ----------------------------- عرض معلومات عامة عن الكورس (للجميع) -----------------------------
    public function publicShow(Course $course)
    {
        $user = auth()->user();
        $isEnrolled = false;

        // هل المستخدم (طالب) مشترك في هذا الكورس؟
        if ($user && method_exists($user, 'enrolledCourses')) {
            $isEnrolled = $user->enrolledCourses->contains($course->id);
        }

        return (new PublicCourseResource($course))
            ->additional([
                'is_enrolled' => $isEnrolled,
            ]);
    }

    // ----------------------------- تعديل كورس -----------------------------
    public function update(UpdateCourseRequest $request, Course $course)
    {
        $user = auth()->user();

        // Teacher → يمكنه تعديل فقط كورساته
        if ($user->role === 'Teacher') {
            if ($course->teacher_id !== $user->id) {
                return response()->json(['message' => 'غير مصرح لك بتعديل هذا الكورس'], 403);
            }
        }

        // Admin → حسب طلبك لا يعدّل الكورسات، فقط يحذفها من مسارات أخرى إن وجدت
        if ($user->role === 'Admin') {
            return response()->json(['message' => 'الأدمن غير مسموح له بتعديل الكورسات من هذا المسار'], 403);
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

    // ----------------------------- حذف كورس -----------------------------
    public function destroy(Course $course)
    {
        $user = auth()->user();

        // Teacher → يحذف فقط كورساته
        if ($user->role === 'Teacher') {
            if ($course->teacher_id !== $user->id) {
                return response()->json(['message' => 'غير مصرح لك بحذف هذا الكورس'], 403);
            }
        }

        // Admin → يمكنه الحذف من هنا أو من UserController حسب المسار
        if ($user->role !== 'Teacher' && $user->role !== 'Admin') {
            return response()->json(['message' => 'غير مصرح لك بحذف هذا الكورس'], 403);
        }

        if ($course->photo) {
            Storage::disk('public')->delete($course->photo);
        }

        $course->delete();

        return response()->json(['message' => 'تم حذف الكورس بنجاح']);
    }

    // ----------------------------- أفضل الكورسات مبيعًا -----------------------------
    public function bestSelling()
    {
        $courses = Course::orderByDesc('sales_count')
                         ->take(5)
                         ->get();

        return response()->json($courses);
    }

    // ----------------------------- قائمة مشترياتي (طالب) -----------------------------
    public function myCourses()
    {
        $user = auth()->user();

        $courses = $user->enrolledCourses()
                        ->with(['teacher', 'lessons'])
                        ->get();

        foreach ($courses as $course) {

            $totalLessons = $course->lessons->count();

            $completedLessons = $user->completedLessons()
                ->whereIn('lesson_id', $course->lessons->pluck('id'))
                ->count();

            $course->progress = $totalLessons > 0
                ? round(($completedLessons / $totalLessons) * 100, 2)
                : 0;

            // هذا الكورس في قائمة مشترياتي → إذن مشترك فيه
            $course->is_enrolled = true;
        }

        return response()->json([
            'user_id' => $user->id,
            'courses' => $courses
        ]);
    }
}
