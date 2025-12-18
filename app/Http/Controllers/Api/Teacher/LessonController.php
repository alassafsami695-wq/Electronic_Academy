<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Services\QuestionGenerationService;
use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Http\Resources\LessonResource;
use App\Http\Requests\UpdateLessonRequest;

class LessonController extends Controller
{
    protected $questionService;

    public function __construct(QuestionGenerationService $questionService)
    {
        $this->questionService = $questionService;
    }

    // ----------------------------- عرض جميع الدروس داخل كورس -----------------------------
    public function index(Course $course)
    {
        $user = auth()->user();

        // Teacher → لا يرى إلا دروس كورساته فقط
        if ($user->role === 'Teacher' && $course->teacher_id !== $user->id) {
            return response()->json(['message' => 'غير مصرح لك بمشاهدة دروس هذا الكورس'], 403);
        }

        // Admin → يمكنه رؤية أي دروس لأي كورس (إن وصل لهذا المسار)
        if ($user->role !== 'Teacher' && $user->role !== 'Admin') {
            return response()->json(['message' => 'غير مصرح لك بمشاهدة الدروس'], 403);
        }

        $lessons = $course->lessons()->orderBy('order')->get();

        return LessonResource::collection($lessons);
    }

    // ----------------------------- عرض درس واحد -----------------------------
    public function show(Course $course, Lesson $lesson)
    {
        $user = auth()->user();

        if ($lesson->course_id !== $course->id) {
            return response()->json(['message' => 'الدرس لا ينتمي لهذا الكورس'], 403);
        }

        // Teacher → لا يرى إلا دروس كورساته
        if ($user->role === 'Teacher' && $course->teacher_id !== $user->id) {
            return response()->json(['message' => 'غير مصرح لك بمشاهدة هذا الدرس'], 403);
        }

        // Admin → يمكنه مشاهدة أي درس (إن وصل لهذا المسار)
        if ($user->role !== 'Teacher' && $user->role !== 'Admin') {
            return response()->json(['message' => 'غير مصرح لك بمشاهدة هذا الدرس'], 403);
        }

        return new LessonResource(
            $lesson->load(['comments.user', 'comments.replies'])
        );
    }

    // ----------------------------- إنشاء درس جديد -----------------------------
    public function store(Request $request, Course $course)
    {
        $user = auth()->user();

        // فقط المدرّس صاحب الكورس يمكنه إضافة درس
        if ($user->role !== 'Teacher' || $course->teacher_id !== $user->id) {
            return response()->json(['message' => 'غير مصرح لك بإنشاء درس لهذا الكورس'], 403);
        }

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
        $user = auth()->user();

        if ($lesson->course_id !== $course->id) {
            return response()->json(['message' => 'الدرس لا ينتمي لهذا الكورس'], 403);
        }

        // Teacher → يمكنه تعديل فقط دروس كورساته
        if ($user->role === 'Teacher') {
            if ($course->teacher_id !== $user->id) {
                return response()->json(['message' => 'غير مصرح لك بتعديل هذا الدرس'], 403);
            }
        }

        // Admin → ممنوع التعديل حسب طلبك
        if ($user->role === 'Admin') {
            return response()->json(['message' => 'الأدمن لا يمكنه تعديل الدروس'], 403);
        }

        $data = $request->validated();

        if ($request->hasFile('video_url')) {
            $data['video_url'] = $request->file('video_url')->store('lessons/videos', 'public');
        }

        $lesson->update($data);

        return (new LessonResource($lesson->fresh()))
            ->additional(['message' => 'تم تعديل الدرس بنجاح']);
    }

    // ----------------------------- حذف درس -----------------------------
    public function destroy(Course $course, Lesson $lesson)
    {
        $user = auth()->user();

        if ($lesson->course_id !== $course->id) {
            return response()->json(['message' => 'الدرس لا ينتمي لهذا الكورس'], 403);
        }

        // Teacher → يحذف فقط دروس كورساته
        if ($user->role === 'Teacher') {
            if ($course->teacher_id !== $user->id) {
                return response()->json(['message' => 'غير مصرح لك بحذف هذا الدرس'], 403);
            }
        }

        // Admin → يمكنه حذف أي درس
        if ($user->role !== 'Teacher' && $user->role !== 'Admin') {
            return response()->json(['message' => 'غير مصرح لك بحذف هذا الدرس'], 403);
        }

        $lesson->delete();

        return response()->json(['message' => 'تم حذف الدرس بنجاح']);
    }

    // ----------------------------- توليد أسئلة لدرس واحد -----------------------------
    public function generateQuestions(Course $course, Lesson $lesson)
    {
        $user = auth()->user();

        if ($lesson->course_id !== $course->id) {
            return response()->json(['message' => 'الدرس لا ينتمي لهذا الكورس'], 403);
        }

        // Teacher صاحب الكورس فقط
        if ($user->role !== 'Teacher' || $course->teacher_id !== $user->id) {
            return response()->json(['message' => 'غير مصرح لك بتوليد أسئلة لهذا الدرس'], 403);
        }

        $questions = $this->questionService->generateQuestions($lesson->content);

        return response()->json([
            'lesson_id' => $lesson->id,
            'lesson_title' => $lesson->title,
            'questions' => $questions
        ]);
    }

    // ----------------------------- توليد أسئلة لجميع الدروس داخل كورس -----------------------------
    public function generateQuestionsForAllLessons(Course $course)
    {
        $user = auth()->user();

        // Teacher صاحب الكورس فقط
        if ($user->role !== 'Teacher' || $course->teacher_id !== $user->id) {
            return response()->json(['message' => 'غير مصرح لك بتوليد أسئلة لهذا الكورس'], 403);
        }

        $lessons = $course->lessons()->orderBy('order')->get();
        $allLessonsData = [];

        foreach ($lessons as $lesson) {
            $questions = $this->questionService->generateQuestions($lesson->content);

            $allLessonsData[] = [
                'lesson_id' => $lesson->id,
                'lesson_title' => $lesson->title,
                'questions' => $questions
            ];
        }

        return response()->json([
            'course_id' => $course->id,
            'course_title' => $course->title,
            'lessons' => $allLessonsData
        ]);
    }

    // ----------------------------- تعليم درس كمكتمل -----------------------------
    public function completeLesson(Lesson $lesson)
    {
        $user = auth()->user();

        // هنا المنطق يخص الطالب الذي يكمل الدرس، وليس المدرّس
        $user->completedLessons()->syncWithoutDetaching([
            $lesson->id => ['completed_at' => now()]
        ]);

        return response()->json([
            'message' => 'تم تعليم الدرس كمكتمل بنجاح',
            'lesson_id' => $lesson->id,
            'completed_at' => now(),
        ]);
    }
}
