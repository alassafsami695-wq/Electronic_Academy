<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Course;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function store(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'required',
            'order' => 'required|integer',
            'video_url' => 'nullable|string',
            'content' => 'nullable|string'
        ]);

        $lesson = $course->lessons()->create($request->all());

        return response()->json($lesson, 201);
    }

    public function update(Request $request, Course $course, Lesson $lesson)
    {
        $lesson->update($request->all());
        return response()->json($lesson);
    }

    public function destroy(Course $course, Lesson $lesson)
    {
        $lesson->delete();
        return response()->json(['message' => 'Lesson deleted']);
    }
}
