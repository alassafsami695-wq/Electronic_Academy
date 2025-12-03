<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
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

    //--------------------------------- جلب التعليقات مع الردود ---------------------
    public function index(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
        ]);

        // جلب التعليقات الجذرية مع الردود
        $comments = Comment::where('lesson_id', $request->lesson_id)
            ->whereNull('parent_id')
            ->with(['user:id,name', 'replies.user:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($comments);
    }
    //------------------------- عرض تعليق واحد -------------------
        public function show($id)
        {
            $comment = Comment::with([
                'user:id,name',
                'replies.user:id,name',
                'lesson:id,title,course_id',
                'lesson.course:id,title,teacher_id',
                'lesson.course.teacher:id,name,email'
            ])->findOrFail($id);

            return response()->json($comment);
        }


    //------------------------- إضافة تعليق جديد أو رد -------------------
    public function store(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'body'      => 'required|string|min:3',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        // إذا كان رد → مسموح فقط للأساتذة
        if ($request->filled('parent_id') && !auth()->user()->isTeacher()) {
            return response()->json(['message' => 'مسموح للأساتذة فقط بالرد'], 403);
        }

        // إنشاء التعليق
        $comment = Comment::create([
            'user_id'   => auth()->id(),
            'lesson_id' => $request->lesson_id,
            'body'      => $request->body,
            'parent_id' => $request->parent_id,
        ]);

        // إرسال إخطار للأستاذ عند تعليق جديد من المستخدم
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
            'message' => 'Comment added successfully.',
            'comment' => $comment->load('user'),
        ], 201);
    }
        public function update(Request $request, $id)
    {
        $request->validate([
            'body' => 'required|string|min:3',
        ]);

        $comment = Comment::findOrFail($id);

        // السماح فقط لصاحب التعليق أو الأستاذ بتعديله
        if ($comment->user_id !== auth()->id() && !auth()->user()->isTeacher()) {
            return response()->json(['message' => 'غير مسموح لك بتعديل هذا التعليق'], 403);
        }

        $comment->update([
            'body' => $request->body,
        ]);

        return response()->json([
            'message' => 'Comment updated successfully.',
            'comment' => $comment->load('user'),
        ]);
    }
        public function destroy($id)
        {
            $comment = Comment::findOrFail($id);

            // السماح فقط لصاحب التعليق أو الأستاذ بحذفه
            if ($comment->user_id !== auth()->id() && !auth()->user()->isTeacher()) {
                return response()->json(['message' => 'غير مسموح لك بحذف هذا التعليق'], 403);
            }

            $comment->delete();

            return response()->json(['message' => 'Comment deleted successfully.']);
        }

}
