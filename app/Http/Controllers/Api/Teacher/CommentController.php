<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Lesson;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
        ]);

        $comments = Comment::where('lesson_id', $request->lesson_id)
            ->whereNull('parent_id')
            ->with(['user:id,name', 'replies.user:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        return CommentResource::collection($comments);
    }

    public function store(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'body'      => 'required|string|min:2',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $user = auth()->user();
        $lesson = Lesson::with('course')->findOrFail($request->lesson_id);
        $courseOwnerId = $lesson->course->teacher_id;

        // منطق الردود الصارم
        if ($request->filled('parent_id')) {
            $parentComment = Comment::findOrFail($request->parent_id);
            
            // تحقق: هل هو الأستاذ صاحب الكورس؟
            $isCourseTeacher = ($user->id === $courseOwnerId);
            
            // تحقق: هل هو الطالب صاحب التعليق الأصلي؟
            $isOriginalCommenter = ($parentComment->user_id === $user->id);

            if (!$isCourseTeacher && !$isOriginalCommenter) {
                return response()->json([
                    'message' => 'عذراً، الرد متاح فقط لأستاذ الكورس أو لصاحب التعليق الأصلي.'
                ], 403);
            }
        }

        $comment = Comment::create([
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