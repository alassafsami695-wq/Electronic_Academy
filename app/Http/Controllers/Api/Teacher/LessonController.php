<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Http\Resources\LessonResource;

class LessonController extends Controller
{
    // ----------------------------- عرض جميع الدروس داخل كورس -----------------------------
    public function index(Course $course)
    {
        $lessons = $course->lessons()->orderBy('order')->get();

        return LessonResource::collection($lessons);
    }

    // ----------------------------- عرض درس واحد داخل كورس -----------------------------
    public function show(Course $course, Lesson $lesson)
    {
        // تأكد أن الدرس ينتمي للكورس
        if ($lesson->course_id !== $course->id) {
            return response()->json(['message' => 'الدرس لا ينتمي لهذا الكورس'], 403);
        }

        return new LessonResource($lesson);
    }

    // ----------------------------- إنشاء درس جديد داخل كورس محدد -----------------------------
    public function store(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'order' => 'required|integer|min:1',
            'video_url' => 'nullable|file|mimetypes:video/mp4,video/avi,video/mpeg|max:204800',
            'content' => 'nullable|string'
        ]);

        $data = $request->only(['title', 'order', 'content']);

        if ($request->hasFile('video_url')) {
            $data['video_url'] = $request->file('video_url')->store('lessons/videos', 'public');
        }

        $data['course_id'] = $course->id;

        $lesson = Lesson::create($data);

        return response()->json([
            'message' => 'تم إنشاء الدرس بنجاح',
            'data' => new LessonResource($lesson)
        ], 201);
    }

    // ----------------------------- تعديل بيانات درس موجود داخل كورس -----------------------------
    public function update(Request $request, Course $course, Lesson $lesson)
    {
        if ($lesson->course_id !== $course->id) {
            return response()->json(['message' => 'الدرس لا ينتمي لهذا الكورس'], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'order' => 'sometimes|required|integer|min:1',
            'video_url' => 'nullable|file|mimetypes:video/mp4,video/avi,video/mpeg|max:204800',
            'content' => 'nullable|string'
        ]);

        $data = $request->only(['title', 'order', 'content']);

        if ($request->hasFile('video_url')) {
            $data['video_url'] = $request->file('video_url')->store('lessons/videos', 'public');
        }

        $lesson->update($data);

        return response()->json([
            'message' => 'تم تعديل الدرس بنجاح',
            'data' => new LessonResource($lesson)
        ]);
    }

    // ----------------------------- حذف درس من كورس -----------------------------
    public function destroy(Course $course, Lesson $lesson)
    {
        if ($lesson->course_id !== $course->id) {
            return response()->json(['message' => 'الدرس لا ينتمي لهذا الكورس'], 403);
        }

        $lesson->delete();

        return response()->json(['message' => 'تم حذف الدرس بنجاح']);
    }
}
