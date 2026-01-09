<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Services\QuestionGenerationService;
use Illuminate\Support\Facades\DB;

class LessonQuestionController extends Controller
{
    // 1) توليد الأسئلة وحفظ سؤال واحد من كل نوع
    public function generateAndStore(Request $request, Lesson $lesson, QuestionGenerationService $service)
    {
      

        // بدلاً من أخذ النص من الـ Request، نأخذه من محتوى الدرس المخزن
        $generated = $service->generateQuestions($lesson->content);
        // مسح الأسئلة القديمة لهذا الدرس لضمان ظهور سؤالين جديدين فقط
        $lesson->questions()->delete();

        $generated = $service->generateQuestions($request->text);

        if (isset($generated['error'])) {
            return response()->json($generated, 500);
        }

        return DB::transaction(function () use ($generated, $lesson) {
            
            // أ- حفظ أول سؤال "اختر من متعدد" فقط (Index 0)
            if (!empty($generated['multiple_choice']['questions'])) {
                $mcq = $generated['multiple_choice']['questions'][0];
                Question::create([
                    'lesson_id' => $lesson->id,
                    'type'      => 'mcq',
                    'question'  => $mcq['question'],
                    'options'   => $mcq['options'],
                    'answer'    => $mcq['answer'],
                ]);
            }

            // ب- حفظ أول سؤال "صح أو خطأ" فقط (Index 0)
            if (!empty($generated['true_false']['questions'])) {
                $tf = $generated['true_false']['questions'][0];
                Question::create([
                    'lesson_id' => $lesson->id,
                    'type'      => 'true_false',
                    'question'  => $tf['question'],
                    'options'   => $tf['options'],
                    'answer'    => $tf['answer'],
                ]);
            }

            return response()->json([
                'message'   => 'تم توليد سؤال واحد "اختر" وسؤال واحد "صح وخطأ" بنجاح',
                'questions' => $generated // الرد يحتوي على كل ما ولده الـ AI، لكن قاعدة البيانات تحفظ 2 فقط
            ], 200, [], JSON_UNESCAPED_UNICODE);
        });
    }

    // 2) عرض الأسئلة (مع حماية الإجابات عن الطالب)
    public function getQuestions(Lesson $lesson) 
    {
        $user = auth()->user();

        $isOwnerOrAdmin = $user->is_super_admin || $lesson->course->teacher_id === $user->id;
        $isEnrolled = $user->enrolledCourses()->where('course_id', $lesson->course_id)->exists();

        if (!$isOwnerOrAdmin && !$isEnrolled) {
            return response()->json([
                'message' => 'يجب عليك الاشتراك في هذا الكورس أولاً للوصول إلى الأسئلة.'
            ], 403);
        }

        // جلب الأسئلة المرتبطة بالدرس
        $questions = $lesson->questions; 

        // إخفاء حقل الإجابة إذا كان المستخدم طالباً
        if (!$isOwnerOrAdmin) {
            $questions->makeHidden(['answer']); 
        }

        return response()->json($questions, 200);
    }

   // 3) تسليم وتصحيح الإجابات
    public function submitAnswers(Request $request, $lessonId)
    {
        $user = auth()->user();
        $studentAnswers = $request->input('answers'); 
        
        if (empty($studentAnswers)) {
            return response()->json(['message' => 'لم يتم إرسال أي إجابات'], 400);
        }

        $score = 0;
        $totalQuestions = count($studentAnswers);

        foreach ($studentAnswers as $answer) {
            $question = Question::find($answer['question_id']);

            if ($question && $question->answer == $answer['selected_option']) {
                $score++;
            }
        }

        $finalPercentage = ($totalQuestions > 0) ? ($score / $totalQuestions) * 100 : 0;

        $lesson = Lesson::find($lessonId);
        if ($lesson) {
            $user->enrolledCourses()->where('course_id', $lesson->course_id)
                 ->updateExistingPivot($lesson->course_id, ['grade' => $finalPercentage]);
        }

        return response()->json([
            'status'     => true,
            'score'      => $score,
            'correct'    => $score, // أضفنا هذا ليتطابق مع طلب التست
            'total'      => $totalQuestions,
            'percentage' => round($finalPercentage, 2),
            'message'    => $finalPercentage >= 50 ? 'أحسنت! لقد نجحت' : 'للأسف، لم تتجاوز الاختبار'
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'questions' => 'required|array',
        ]);

        foreach ($request->questions as $q) {
            Question::create([
                'lesson_id' => $request->lesson_id,
                'type'      => $q['type'],
                'question'  => $q['question'],
                'options'   => $q['options'] ?? null,
                'answer'    => $q['answer'],
            ]);
        }

        return response()->json(['message' => 'Questions saved successfully'], 200);
    }
}