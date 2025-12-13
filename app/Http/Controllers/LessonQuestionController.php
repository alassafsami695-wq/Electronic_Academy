<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Services\QuestionGenerationService;

class LessonQuestionController extends Controller
{
    // 1) Generate questions using Python and store them
    public function generateAndStore(Request $request, Lesson $lesson, QuestionGenerationService $service)
    {
        $request->validate([
            'text' => 'required|string'
        ]);

        // Generate questions using Python
        $generated = $service->generateQuestions($request->text);

        if (isset($generated['error'])) {
            return response()->json($generated, 500);
        }

        // Store MCQ questions
        foreach ($generated['multiple_choice']['questions'] as $q) {
            Question::create([
                'lesson_id' => $lesson->id,
                'type' => 'mcq',
                'question' => $q['question'],
                'options' => $q['options'],
                'answer' => $q['answer'],
            ]);
        }

        // Store True/False questions
        foreach ($generated['true_false']['questions'] as $q) {
            Question::create([
                'lesson_id' => $lesson->id,
                'type' => 'true_false',
                'question' => $q['question'],
                'options' => $q['options'],
                'answer' => $q['answer'],
            ]);
        }

        return response()->json([
            'message' => 'Questions generated and stored successfully',
            'questions' => $generated
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    // 2) Store questions manually (optional)
    public function store(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'questions' => 'required|array',
        ]);

        foreach ($request->questions as $q) {
            Question::create([
                'lesson_id' => $request->lesson_id,
                'type' => $q['type'],
                'question' => $q['question'],
                'options' => $q['options'] ?? null,
                'answer' => $q['answer'],
            ]);
        }

        return response()->json(['message' => 'Questions saved successfully'], 200);
    }

    // 3) Submit student answers
    public function submitAnswers(Request $request)
    {
        $request->validate([
            'answers' => 'required|array',
        ]);

        $correct = 0;
        $total = count($request->answers);

        foreach ($request->answers as $item) {
            $question = Question::find($item['question_id']);

            if ($question && trim($question->answer) === trim($item['answer'])) {
                $correct++;
            }
        }

        $score = ($correct / $total) * 100;
        $passed = $score >= 50;

        return response()->json([
            'correct' => $correct,
            'total' => $total,
            'score' => $score,
            'status' => $passed ? 'passed' : 'failed'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

        public function getQuestions(Lesson $lesson)
    {
        $questions = $lesson->questions()->select('id', 'type', 'question', 'options')->get();

        return response()->json([
            'lesson_id' => $lesson->id,
            'questions' => $questions
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

}
