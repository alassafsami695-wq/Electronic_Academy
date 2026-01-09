<?php

namespace Tests\Feature;

use App\Models\{User, Course};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CourseLessonTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function teacher_can_add_lesson_to_their_course()
    {
        // إنشاء مدرس وكورس يملكه
        $teacher = User::factory()->create(['role_id' => 2]); 
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);

        $lessonData = [
            'title' => 'الدرس الأول: مقدمة',
            'content' => 'محتوى الدرس التجريبي هنا...',
            'order' => 1, 
        ];

        $response = $this->actingAs($teacher)
                         ->postJson("/api/teacher/courses/{$course->id}/lessons", $lessonData);

        $response->assertSuccessful(); 
        
        $this->assertDatabaseHas('lessons', [
            'title' => 'الدرس الأول: مقدمة',
            'course_id' => $course->id,
            'order' => 1
        ]);
    }
}