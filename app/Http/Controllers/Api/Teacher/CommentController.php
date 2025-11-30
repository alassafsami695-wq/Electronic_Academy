<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Comment;
use App\Notifications\NewCommentNotification;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    
     //--------------------------------- إعادة تعليقات درس معين (جذرية + الردود)---------------------
     
    public function index(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
        ]);

        $lessonId = $request->lesson_id;

        $comments = Comment::where('lesson_id', $lessonId)
            ->whereNull('parent_id')
            ->with(['user:id,name', 'replies.user:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($comments);
    }

    // -------------------------تخزين تعليق جديد أو رد-------------------
     
    public function store(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'body' => 'required|string|min:3',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        // إذا كان هو رد - تأكد من الصلاحية
        if ($request->filled('parent_id')) {
            $this->authorize('replyToComment', Comment::class);
        }

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'lesson_id' => $request->lesson_id,
            'body' => $request->body,
            'parent_id' => $request->parent_id,
        ]);

        // ---------------------------إرسال إخطار للمعلم المرتبط بالكورس------------------ 
        if ($comment->lesson && $comment->lesson->course && $comment->lesson->course->teacher) {
            $teacher = $comment->lesson->course->teacher;
            $teacher->notify(new NewCommentNotification($comment));
        }

        return response()->json([
            'message' => 'Comment added successfully.',
            'comment' => $comment->load('user'),
        ], 201);
    }

    
}
