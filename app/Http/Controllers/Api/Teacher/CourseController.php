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
    public function index()
    {
        $courses = Course::with(['teacher', 'path'])->paginate(10);
        return CourseResource::collection($courses);
    }

        public function store(StoreCourseRequest $request)
        {
            // ✅ التحقق من البيانات القادمة من StoreCourseRequest
            $data = $request->validated();

            // ✅ ربط الكورس بالمدرّس المسجل دخول
            $data['teacher_id'] = auth()->id();

            // 📸 رفع الصورة إن وجدت
            if ($request->hasFile('photo')) {
                $data['photo'] = $request->file('photo')->store('courses', 'public');
            }

            // ➕ إنشاء الكورس
            $course = Course::create($data);

            // ✅ إرجاع الكورس كـ Resource
            return new CourseResource($course);
        }




    public function show(Course $course)
    {
        return new CourseResource(
            $course->load(['teacher', 'path', 'lessons'])
        );
    }


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

    public function destroy(Course $course)
    {
        if ($course->photo) {
            Storage::disk('public')->delete($course->photo);
        }

        $course->delete();

        return response()->json(['message' => 'Course deleted successfully']);
    }

    public function bestSelling()
    {
        $courses = Course::orderByDesc('sales_count')
                         ->take(5)
                         ->get();

        return response()->json($courses);
    }
    public function myCourses()
    {
        $user = auth()->user();

        $courses = $user->enrolledCourses()->with('teacher')->get();

        return response()->json([
            'user_id' => $user->id,
            'courses' => $courses
        ]);
    }

}
