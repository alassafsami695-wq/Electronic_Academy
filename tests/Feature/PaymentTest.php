<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Path;
use App\Models\Lesson;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_purchase_course_with_enough_balance()
    {
        $studentRole = Role::firstOrCreate(['name' => 'user']);
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);

        $path = Path::factory()->create();
        $teacher = User::factory()->create(['role_id' => $teacherRole->id]);
        $teacher->wallet()->create(['balance' => 0]);

        $course = Course::factory()->create([
            'price' => 1000,
            'teacher_id' => $teacher->id,
            'path_id' => $path->id,
        ]);

        $student = User::factory()->create(['role_id' => $studentRole->id]);
        $student->wallet()->create(['balance' => 2000]);

        $response = $this->actingAs($student, 'sanctum')
                         ->postJson("/api/courses/{$course->id}/purchase");

        $response->assertStatus(200);
    }

    public function test_generate_questions_validation()
    {
        // تم استخدام الأسماء الافتراضية للأدوار، تأكد أنها تطابق قاعدة بياناتك
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        
        $teacher = User::factory()->create([
            'role_id' => $teacherRole->id,
            'status' => 'active' // نضمن أنه غير معلق
        ]); 
        
        $path = Path::factory()->create();
        $course = Course::factory()->create([
            'teacher_id' => $teacher->id, 
            'path_id' => $path->id
        ]);
        
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        $response = $this->actingAs($teacher, 'sanctum')
                         ->postJson("/api/teacher/lessons/{$lesson->id}/questions/generate", [
                             'text' => '' 
                         ]);

        $response->assertStatus(422); 
    }
}