<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lesson;
use Carbon\Carbon;

class LessonCompletionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * علامة إكمال درس (route model binding)
     */
    public function markAsCompleted(Lesson $lesson)
    {
        $user = auth()->user();

        if (! $user->enrolledCourses()->where('course_id', $lesson->course_id)->exists()) {
            return response()->json(['message' => 'Forbidden. you are not enrolled in this course'], 403);
        }

        // افترض أنّ العلاقة completedLessons() علاقة many-to-many مع pivot يحتوي على completed_at
        $user->completedLessons()->updateOrCreate(
            ['lesson_id' => $lesson->id],
            ['completed_at' => Carbon::now()]
        );

        return response()->json(['message' => 'Lesson marked as completed'], 200);
    }
}
