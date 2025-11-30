<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Course;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    
    //----------------------------- إنشاء درس جديد داخل كورس محدد---------------------------
     
    public function store(Request $request, Course $course)
    {
        //------------------------------التحقق من صحة البيانات المرسلة------------------------
        $request->validate([
            'title' => 'required',
            'order' => 'required|integer',
            'video_url' => 'nullable|string',
            'content' => 'nullable|string'
        ]);

        // ------------------------------إنشاء الدرس وربطه بالكورس-----------------
        $lesson = $course->lessons()->create($request->all());

        // ---------------------------إرجاع الدرس الجديد كـ JSON--------------------
        return response()->json($lesson, 201);
    }

    
    //----------------------------تعديل بيانات درس موجود داخل كورس------------------
    
    public function update(Request $request, Course $course, Lesson $lesson)
    {
        $lesson->update($request->all());

        return response()->json($lesson);
    }
    
     // --------------------------------حذف درس من كورس-------------------
     
    public function destroy(Course $course, Lesson $lesson)
    {
        // حذف الدرس
        $lesson->delete();

        // إرجاع رسالة نجاح
        return response()->json(['message' => 'Lesson deleted']);
    }
}
