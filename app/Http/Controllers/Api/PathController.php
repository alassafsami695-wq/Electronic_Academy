<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PathResource;
use Illuminate\Http\Request;
use App\Models\Path;

class PathController extends Controller
{
    // عرض جميع المسارات
    public function index()
    {
        return Path::all();
    }

    // عرض تفاصيل مسار مع الكورسات والتحقق من الاشتراك
    public function show(Path $path)
    {
        $path->load(['courses.teacher', 'courses.lessons']);

        $user = auth()->user();

        if ($user) {
            $enrolledCourseIds = $user->enrolledCourses()->pluck('courses.id')->toArray();

            $path->courses->each(function ($course) use ($enrolledCourseIds) {
                $course->is_enrolled = in_array($course->id, $enrolledCourseIds);
            });
        } else {
            $path->courses->each(function ($course) {
                $course->is_enrolled = false;
            });
        }

        return new PathResource($path);
    }

    // عرض المسار للعامة
    public function publicShow(Path $path)
    {
        $path->load(['courses.teacher', 'courses.lessons']);
        return new PathResource($path);
    }

    // إنشاء مسار جديد
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255|unique:paths,title',
            'description' => 'nullable|string',
            'tips'        => 'nullable|array',
        ]);

        $path = Path::create([
            'title'       => $request->title,
            'description' => $request->description,
            'tips'        => $request->tips,
        ]);

        return response()->json([
            'message' => 'Path created successfully',
            'data'    => $path
        ], 201);
    }

    // تعديل مسار
    public function update(Request $request, Path $path)
    {
        $request->validate([
            'title'       => 'sometimes|string|max:255|unique:paths,title,' . $path->id,
            'description' => 'sometimes|string|nullable',
            'tips'        => 'sometimes|array|nullable',
        ]);

        $path->update($request->only([
            'title',
            'description',
            'tips',
        ]));

        return response()->json([
            'message' => 'Path updated successfully',
            'data'    => $path
        ]);
    }

    // حذف مسار والكورسات التابعة له
    public function destroy(Path $path)
    {
        $path->courses()->delete();
        $path->delete();

        return response()->json([
            'message' => 'Path and its courses deleted successfully'
        ]);
    }

    // جلب الكورسات ضمن المسار
    public function course(Path $path)
    {
        $courses = $path->courses()->select('id', 'title', 'description', 'price', 'photo')->get();

        return response()->json([
            'path_id'    => $path->id,
            'path_title' => $path->title,
            'courses'    => $courses
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
