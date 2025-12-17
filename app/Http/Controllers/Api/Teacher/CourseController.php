<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    // ----------------------------- عرض جميع الكورسات -----------------------------
    public function index()
    {
        $courses = Course::with(['teacher', 'path'])->paginate(10);
        return CourseResource::collection($courses);
    }

    // ----------------------------- إنشاء كورس جديد -----------------------------
    public function store(StoreCourseRequest $request)
    {
        $data = $request->validated();

        // ربط الكورس بالمدرّس
        $data['teacher_id'] = auth()->id();

        // رفع الصورة إن وجدت
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('courses', 'public');
        }

        $course = Course::create($data);

        return new CourseResource($course);
    }

    // ----------------------------- عرض كورس واحد -----------------------------
    public function show(Course $course)
    {
        return new CourseResource(
            $course->load(['teacher', 'path', 'lessons'])
        );
    }

    // ----------------------------- تعديل كورس -----------------------------
    public function update(UpdateCourseRequest $request, Course $course)
    {
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
        if ($course->photo) {
            Storage::disk('public')->delete($course->photo);
        }

        $course->delete();

        return response()->json(['message' => 'Course deleted successfully']);
    }

    // ----------------------------- أفضل الكورسات مبيعًا -----------------------------
    public function bestSelling()
    {
        $courses = Course::orderByDesc('sales_count')
                         ->take(5)
                         ->get();

        return response()->json($courses);
    }

    // ----------------------------- قائمة مشترياتي -----------------------------
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
