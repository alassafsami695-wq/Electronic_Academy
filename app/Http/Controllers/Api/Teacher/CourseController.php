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
    public function index()
    {
        $user = auth()->user();

        $courses = Course::with(['teacher', 'path'])
            ->where('teacher_id', $user->id)
            ->paginate(10);

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
        if ($course->teacher_id !== $user->id) {
            return response()->json(['message' => 'غير مصرح لك'], 403);
        }

        $course->load(['teacher', 'path', 'lessons']);

        return new CourseResource($course);
    }

    public function publicShow(Course $course)
    {
        $course->load(['teacher', 'path', 'lessons']);

        return (new PublicCourseResource($course))
            ->additional([
                'is_enrolled' => auth()->check()
                    ? auth()->user()->enrolledCourses()->where('courses.id', $course->id)->exists()
                    : false,
            ]);
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
        if ($course->teacher_id !== $user->id) {
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

            $course->is_enrolled = true;
        }

        return response()->json([
            'user_id' => $user->id,
            'courses' => $courses
        ]);
    }
}
