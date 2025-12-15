<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Notifications\NewCommentNotification;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct()
    {
        // جميع العمليات تتطلب تسجيل دخول
        $this->middleware('auth:sanctum');
    }

    // -------------------- جلب كل التعليقات الجذرية مع الردود --------------------
    public function index(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
        ]);

        $comments = Comment::where('lesson_id', $request->lesson_id)
            ->whereNull('parent_id')
            ->with([
                'user:id,name,email',
                'replies.user:id,name,email',
                'replies.replies.user:id,name,email'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return CommentResource::collection($comments);
    }

    // -------------------- عرض تعليق واحد مع كل الردود --------------------
    public function show($id)
    {
        $comment = Comment::with([
            'user:id,name,email',
            'replies.user:id,name,email',
            'replies.replies.user:id,name,email',
            'lesson:id,title,course_id',
            'lesson.course:id,title,teacher_id',
            'lesson.course.teacher:id,name,email'
        ])->findOrFail($id);

        return new CommentResource($comment);
    }

    // -------------------- إضافة تعليق أو رد --------------------
    public function store(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'body'      => 'required|string|min:3',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        // الردود مسموحة فقط للأساتذة
        if ($request->filled('parent_id') && !auth()->user()->isTeacher()) {
            return response()->json(['message' => 'مسموح للأساتذة فقط بالرد'], 403);
        }

        $comment = Comment::create([
            'user_id'   => auth()->id(),
            'lesson_id' => $request->lesson_id,
            'body'      => $request->body,
            'parent_id' => $request->parent_id,
        ]);

        // إخطار الأستاذ عند تعليق جديد من مستخدم
        if (
            !$request->filled('parent_id') &&
            $comment->lesson &&
            $comment->lesson->course &&
            $comment->lesson->course->teacher
        ) {
            $teacher = $comment->lesson->course->teacher;
            $teacher->notify(new NewCommentNotification($comment));
        }

        return response()->json([
            'message' => 'تم إضافة التعليق بنجاح.',
            'comment' => new CommentResource(
                $comment->fresh(['user', 'lesson', 'replies'])
            )
        ], 201);
    }

    // -------------------- تعديل تعليق --------------------
    public function update(Request $request, $id)
    {
        $request->validate([
            'body' => 'required|string|min:3',
        ]);

        $comment = Comment::findOrFail($id);

        // السماح فقط لصاحب التعليق أو الأستاذ
        if ($comment->user_id !== auth()->id() && !auth()->user()->isTeacher()) {
            return response()->json(['message' => 'غير مسموح لك بتعديل هذا التعليق'], 403);
        }

        $comment->update([
            'body' => $request->body,
        ]);

        return response()->json([
            'message' => 'تم تعديل التعليق بنجاح.',
            'comment' => new CommentResource($comment->fresh(['user']))
        ]);
    }

    // -------------------- حذف تعليق --------------------
    public function destroy(Comment $comment)
    {
        // السماح فقط لصاحب التعليق أو الأستاذ أو الأدمن
        if (
            $comment->user_id !== auth()->id() &&
            !auth()->user()->isTeacher() &&
            !auth()->user()->isAdmin()
        ) {
            return response()->json(['message' => 'غير مسموح لك بحذف هذا التعليق'], 403);
        }

        $comment->replies()->delete();
        $comment->delete();

        return response()->json([
            'message' => 'تم حذف التعليق والردود التابعة بنجاح.'
        ]);
    }
}
