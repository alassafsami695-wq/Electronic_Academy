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
    // المدرّس يشاهد كورساته فقط
    public function index()
    {
        $user = auth()->user();

        $courses = Course::with(['teacher', 'path'])
            ->where('teacher_id', $user->id)
            ->paginate(10);

        return CourseResource::collection($courses);
    }

    // إنشاء كورس جديد
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

    // عرض كورس واحد
    public function show(Course $course)
    {
        $user = auth()->user();

        if ($user->isAdmin() || $course->teacher_id === $user->id) {
            $course->load(['teacher', 'path', 'lessons']);
            return new CourseResource($course);
        }

        if ($user->isStudent()) {
            $isSubscribed = $course->students()
                ->where('student_id', $user->id)
                ->exists();

            if ($isSubscribed) {
                // الطالب مشترك → يرى الكورس والدروس
                $course->load(['teacher', 'path', 'lessons']);
                return new CourseResource($course);
            }

            // الطالب غير مشترك → لا يرى الكورس هنا
            return response()->json(['message' => 'يمكنك رؤية هذا الكورس فقط في قائمة مشترياتي'], 403);
        }

        return response()->json(['message' => 'غير مصرح لك'], 403);
    }
   


    // عرض الكورس للعامة (بدون دروس أو اشتراك)
    public function publicShow(Course $course)
    {
        $course->load(['teacher', 'path']);
        return new PublicCourseResource($course);
    }

    // تعديل كورس
    public function update(UpdateCourseRequest $request, Course $course)
    {
        $user = auth()->user();

        // Teacher فقط
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

        // حذف كورس
    public function destroy(Course $course)
    {
        $user = auth()->user();

        // Teacher أو Admin فقط
        if ($course->teacher_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['message' => 'غير مصرح لك بحذف هذا الكورس'], 403);
        }

        if ($course->photo) {
            Storage::disk('public')->delete($course->photo);
        }

        $course->delete();
        return response()->json(['message' => 'تم حذف الكورس بنجاح']);
    }


    // أفضل الكورسات مبيعًا
    public function bestSelling()
    {
        return response()->json(
            Course::orderByDesc('sales_count')->take(5)->get()
        );
    }

    // قائمة مشترياتي (الطالب فقط)
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
