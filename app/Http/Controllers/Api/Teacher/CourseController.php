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
     * عرض الكورسات مع دعم البحث باسم الكورس
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Course::with(['teacher', 'path']);

        // 🔍 منطق البحث باسم الكورس (يرتبط بشريط البحث في الواجهة)
        if ($request->has('search') && !empty($request->search)) {
            $query->where('title', 'LIKE', '%' . $request->search . '%');
        }

        // إذا كان مدرساً، يعرض له كورساته فقط إلا إذا طلب البحث العام
        if ($user && $user->isTeacher() && !$request->has('public')) {
            $query->where('teacher_id', $user->id);
        }

        $courses = $query->latest()->paginate(10);

        return CourseResource::collection($courses);
    }

    public function store(StoreCourseRequest $request)
    {
        $user = auth()->user();
        $data = $request->validated();
        $data['teacher_id'] = $user->id;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('courses', 'public');
        }

        $course = Course::create($data);

        return new CourseResource($course);
    }

    public function show(Course $course)
    {
        $user = auth()->user();

        if ($user->isAdmin() || $course->teacher_id === $user->id) {
            $course->load(['teacher', 'path', 'lessons']);
            return new CourseResource($course);
        }

        // استخدام العلاقة الصحيحة للتحقق من اشتراك الطالب
        $isSubscribed = $course->enrolledUsers()
            ->where('user_id', $user->id)
            ->exists();

        if ($isSubscribed) {
            $course->load(['teacher', 'path', 'lessons']);
            return new CourseResource($course);
        }

        return response()->json(['message' => 'يمكنك رؤية هذا الكورس فقط في قائمة مشترياتي'], 403);
    }

    public function publicShow(Course $course)
    {
        $course->load(['teacher', 'path']);
        return new PublicCourseResource($course);
    }

    public function update(UpdateCourseRequest $request, Course $course)
    {
        $user = auth()->user();

        if ($course->teacher_id !== $user->id) {
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

    public function destroy(Course $course)
    {
        $user = auth()->user();

        if ($course->teacher_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['message' => 'غير مصرح لك بحذف هذا الكورس'], 403);
        }

        if ($course->photo) {
            Storage::disk('public')->delete($course->photo);
        }

        $course->delete();
        return response()->json(['message' => 'تم حذف الكورس بنجاح']);
    }

    public function bestSelling()
    {
        return response()->json(
            Course::orderByDesc('sales_count')->take(5)->get()
        );
    }

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
        }

        return response()->json([
            'user_id' => $user->id,
            'courses' => $courses
        ]);
    }
}