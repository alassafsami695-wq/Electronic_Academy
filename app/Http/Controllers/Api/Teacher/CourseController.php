<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Path;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('is.Teacher'); 
    }

    public function index(Request $request)
    {
        $query = Course::query()->where('is_published', true);

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('path_id')) {
            $query->where('path_id', $request->path_id);
        }

        if ($request->wantsJson()) {
            $courses = $query->with('path', 'teacher')->paginate(10);
            return response()->json($courses);
        }

        $courses = $query->with('path', 'teacher')->paginate(10);
        $paths = Path::all();

        return view('courses.index', compact('courses', 'paths'));
    }

    public function show(Course $course)
    {
        $this->authorize('update', $course);
        return response()->json($course->load('lessons'));
    }

    public function create()
    {
        return view('teacher.courses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'required|string',
            'price' => 'required|numeric|min:0',
            'path_id' => 'required|exists:paths,id',
        ]);

        $course = Course::create([
            'title' => $request->title,
            'summary' => $request->summary,
            'path_id' => $request->path_id,
            'teacher_id' => auth()->id(),
            'is_published' => false,
        ]);

        return redirect()->route('teacher.courses.index')->with('success', 'تم إضافة الكورس بنجاح');
    }

    public function update(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'summary' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'path_id' => 'sometimes|required|exists:paths,id',
            'is_published' => 'sometimes|boolean',
        ]);

        $course->update($data);

        return response()->json(['message' => 'Course updated successfully', 'course' => $course]);
    }

    public function destroy(Course $course)
    {
        $this->authorize('delete', $course);
        $course->delete();
        return response()->json(['message' => 'Course deleted successfully'], 200);
    }
}
