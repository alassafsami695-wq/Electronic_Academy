<?php

namespace Tests\Feature;

use App\Models\{User, Lesson, Question};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class FinalBusinessLogicTest extends TestCase
{
    use RefreshDatabase;

    #[Test]


    public function test_student_can_submit_lesson_quiz()
    {
        $student = User::factory()->create();
        $lesson = \App\Models\Lesson::factory()->create();
        $question = \App\Models\Question::factory()->create(['lesson_id' => $lesson->id, 'answer' => 'A']);

        $response = $this->actingAs($student)->postJson("/api/lessons/{$lesson->id}/questions/submit", [
            'answers' => [
            ['question_id' => $question->id, 'selected_option' => 'A']            ]
        ]);

        $response->assertStatus(200);
    }
}