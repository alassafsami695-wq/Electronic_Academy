<?php

namespace Tests\Feature;

use App\Models\{User, Lesson, Question};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LessonQuizTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function student_can_submit_quiz_answers()
    {
        $student = User::factory()->create();
        $lesson = Lesson::factory()->create();
        $question = Question::factory()->create([
            'lesson_id' => $lesson->id,
            'answer' => 'A',
            'type' => 'mcq'
        ]);

        // تأكد من ربط الطالب بالكورس لتجنب خطأ في الكنترولر عند التحديث
        $student->enrolledCourses()->attach($lesson->course_id, ['grade' => 0]);

        $response = $this->actingAs($student)->postJson("/api/lessons/{$lesson->id}/questions/submit", [
            'answers' => [
                ['question_id' => $question->id, 'selected_option' => 'A']
            ]
        ]);

        $response->assertStatus(200);

        // نتحقق من وجود المفاتيح التي يطلبها التست ويرسلها الكنترولر
        $response->assertJsonStructure([
            'status',
            'score',
            'correct', // هذا المهم
            'total',
            'percentage',
            'message'
        ]);

        $response->assertJsonFragment(['status' => true]);
    }
}