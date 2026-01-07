<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Lesson;
use App\Models\Course;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * عرض التعليقات:
     * 1. للأدمن: عرض كل تعليقات كورس معين (بناءً على course_id).
     * 2. للطلاب/المدرسين: عرض تعليقات درس معين (بناءً على lesson_id).
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // ميزة الأدمن: عرض تعليقات الكورس كاملاً مجمعة حسب الدروس
        if ($request->has('course_id') && $user->isAdmin()) {
            $course = Course::with(['lessons.comments' => function($query) {
                $query->whereNull('parent_id')
                      ->with(['user:id,name', 'replies.user:id,name'])
                      ->orderBy('created_at', 'desc');
            }])->findOrFail($request->course_id);

            $data = $course->lessons->map(function ($lesson) {
                return [
                    'lesson_id'    => $lesson->id,
                    'lesson_title' => $lesson->title,
                    'comments'     => CommentResource::collection($lesson->comments)
                ];
            })->filter(fn($l) => $l['comments']->count() > 0)->values();

            return response()->json([
                'status' => 'success',
                'view'   => 'Admin_Course_Full_View',
                'course' => $course->title,
                'data'   => $data
            ]);
        }

        // الحالة الافتراضية: عرض تعليقات درس معين
        if ($request->has('lesson_id')) {
            $comments = Comment::where('lesson_id', $request->lesson_id)
                ->whereNull('parent_id')
                ->with(['user:id,name', 'replies.user:id,name'])
                ->orderBy('created_at', 'desc')
                ->get();

            return CommentResource::collection($comments);
        }

        return response()->json(['message' => 'يجب إرسال lesson_id أو course_id (للأدمن)'], 400);
    }

  public function store(Request $request)
{
    $request->validate([
        'lesson_id' => 'required|exists:lessons,id',
        'body'      => 'required|string|min:2',
        'parent_id' => 'nullable|exists:comments,id',
    ]);

    $user = auth()->user();
    $lesson = \App\Models\Lesson::with('course')->findOrFail($request->lesson_id);
    
    // التحقق عند وجود رد (parent_id)
    if ($request->filled('parent_id')) {
        $parentComment = \App\Models\Comment::findOrFail($request->parent_id);
        
        $isCourseTeacher = ($user->id === $lesson->course->teacher_id);
        $isOriginalCommenter = ($parentComment->user_id === $user->id);

        // المنطق الجديد:
        // يُمنع الرد إذا لم يكن المستخدم هو أستاذ الكورس "و" ليس صاحب التعليق الأصلي
        // (حتى لو كان أدمن، سيتم منعه هنا لأننا استبعدنا شرط الأدمن)
        if (!$isCourseTeacher && !$isOriginalCommenter) {
            return response()->json([
                'message' => 'عذراً، الرد متاح فقط لأستاذ الكورس أو صاحب التعليق الأصلي.'
            ], 403);
        }
    }

    $comment = \App\Models\Comment::create([
        'user_id'   => $user->id,
        'lesson_id' => $request->lesson_id,
        'body'      => $request->body,
        'parent_id' => $request->parent_id,
    ]);

    return response()->json([
        'message' => 'تمت الإضافة بنجاح',
        'comment' => $comment->load('user:id,name')
    ], 201);
}

    public function destroy(Comment $comment)
    {
        $lesson = Lesson::with('course')->findOrFail($comment->lesson_id);

        if (auth()->id() !== $comment->user_id && 
            !auth()->user()->isAdmin() && 
            auth()->id() !== $lesson->course->teacher_id) {
            return response()->json(['message' => 'غير مصرح لك بحذف هذا التعليق'], 403);
        }

        $comment->delete();
        return response()->json(['message' => 'تم حذف التعليق بنجاح']);
    }
}