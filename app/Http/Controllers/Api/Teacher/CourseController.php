<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Path;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('is.Teacher'); 
    }

   
    public function index(Request $request)
    {
        $query = Course::query()->with('teacher', 'path');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('path_id')) {
            $query->where('path_id', $request->path_id);
        }

        $courses = $query->paginate(10);

        return response()->json([
            'data' => $courses->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'photo' => $course->photo,
                    'price' => $course->price,
                    'course_duration' => $course->course_duration,
                    'number_of_students' => $course->number_of_students,
                    'teacher_name' => $course->teacher->name ?? null,
                    'path_title' => $course->path->title ?? null,
                ];
            }),
            'pagination' => [
                'current_page' => $courses->currentPage(),
                'last_page' => $courses->lastPage(),
                'total' => $courses->total(),
            ]
        ]);
    }

   //--------------------------------رؤية كورس معين----------------------- 
    public function show(Course $course)
    {
        return response()->json([
            'id' => $course->id,
            'title' => $course->title,
            'description' => $course->description,
            'photo' => $course->photo,
            'price' => $course->price,
            'course_duration' => $course->course_duration,
            'number_of_students' => $course->number_of_students,
            'teacher_name' => $course->teacher->name ?? null,
            'path_title' => $course->path->title ?? null,
            'lessons' => $course->lessons,
        ]);
    }

    //----------------------------إضافة كورس-----------------------
    public function store(Request $request)
    {
        $request->validate([
            'title'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'photo'             => 'nullable|string',
            'price'             => 'required|numeric|min:0',
            'course_duration'   => 'nullable|string',
            'number_of_students'=> 'nullable|integer|min:0',
            'rating'            => 'nullable|numeric|min:0|max:5',
            'teacher_id'        => 'required|exists:users,id',
            'path_id'           => 'required|exists:paths,id',
        ]);

        $course = Course::create($request->only([
            'title',
            'description',
            'photo',
            'price',
            'course_duration',
            'number_of_students',
            'rating',
            'teacher_id',
            'path_id',
        ]));

        return response()->json([
            'message' => 'Course created successfully',
            'data'    => $course->load('teacher', 'path')
        ], 201);
    }

    //----------------------------تعديل كورس--------------------------- 
    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'title'             => 'sometimes|required|string|max:255',
            'description'       => 'nullable|string',
            'photo'             => 'nullable|string',
            'price'             => 'sometimes|required|numeric|min:0',
            'course_duration'   => 'nullable|string',
            'number_of_students'=> 'nullable|integer|min:0',
            'rating'            => 'nullable|numeric|min:0|max:5',
            'teacher_id'        => 'sometimes|required|exists:users,id',
            'path_id'           => 'sometimes|required|exists:paths,id',
            'is_published'      => 'sometimes|boolean',
        ]);

        $course->update($data);

        return response()->json([
            'message' => 'Course updated successfully',
            'data'    => $course->load('teacher', 'path')
        ]);
    }

   //------------------------------حذف كورس---------------------- 
    public function destroy(Course $course)
    {
        $course->delete();
        return response()->json(['message' => 'Course deleted successfully'], 200);
    }
}
