<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Http\Resources\LessonResource;
use App\Http\Requests\UpdateLessonRequest;

class LessonController extends Controller
{
    // ----------------------------- عرض جميع الدروس داخل كورس -----------------------------
    public function index(Course $course)
    {
        $lessons = $course->lessons()->orderBy('order')->get();
        return LessonResource::collection($lessons);
    }

    // ----------------------------- عرض درس واحد -----------------------------
   
    public function show(Course $course, Lesson $lesson)
    {
        if ($lesson->course_id !== $course->id) {
                return response()->json(['message' => 'الدرس لا ينتمي لهذا الكورس'], 403);
            }

        return new LessonResource(
            $lesson->load(['comments.user', 'comments.replies'])
        );
    }


    // ----------------------------- إنشاء درس جديد (لا نعدل عليها) -----------------------------
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

        return (new LessonResource($lesson))
            ->additional(['message' => 'تم إنشاء الدرس بنجاح'])
            ->response()
            ->setStatusCode(201);
    }

    // ----------------------------- تعديل درس -----------------------------
    public function update(UpdateLessonRequest $request, Course $course, Lesson $lesson)
    {
        if ($lesson->course_id !== $course->id) {
            return response()->json(['message' => 'الدرس لا ينتمي لهذا الكورس'], 403);
        }

        // اجلب فقط الحقول المرسلة
        $data = $request->validated();

        // تحديث الفيديو فقط إذا تم إرساله
        if ($request->hasFile('video_url')) {
            $data['video_url'] = $request->file('video_url')->store('lessons/videos', 'public');
        }

        // تحديث الدرس
        $lesson->update($data);

        return (new LessonResource($lesson->fresh()))
            ->additional(['message' => 'تم تعديل الدرس بنجاح']);
    }

    // ----------------------------- حذف درس -----------------------------
    public function destroy(Course $course, Lesson $lesson)
    {
        if ($lesson->course_id !== $course->id) {
            return response()->json(['message' => 'الدرس لا ينتمي لهذا الكورس'], 403);
        }

        $lesson->delete();

        return response()->json(['message' => 'تم حذف الدرس بنجاح']);
    }
}
