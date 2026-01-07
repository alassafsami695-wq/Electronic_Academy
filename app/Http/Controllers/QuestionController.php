<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\QuestionGenerationService;

class QuestionController extends Controller
{
    protected $questionService;

    public function __construct(QuestionGenerationService $questionService)
    {
        $this->questionService = $questionService;
    }

    public function generate(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
        ]);

        $text = $request->input('text');

        $questions = $this->questionService->generateQuestions($text);

        // دعم UTF-8 لمنع مشاكل الترميز
        return response()->json($questions, 200, [], JSON_UNESCAPED_UNICODE);
    }
}
