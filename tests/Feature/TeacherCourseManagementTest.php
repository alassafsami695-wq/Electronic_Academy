<?php

namespace Tests\Feature;

use App\Models\{User, Role, Course};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class TeacherCourseManagementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]

    public function test_teacher_can_create_new_course()
    {
        // 1. التجهيز
        $teacherRole = \App\Models\Role::firstOrCreate(['name' => 'teacher']);
        $teacher = \App\Models\User::factory()->create([
            'role_id' => $teacherRole->id,
            'is_verified' => true,
            'status' => 'active'
        ]);
        
        $path = \App\Models\Path::firstOrCreate(['title' => 'Web Development']);

        // 2. التنفيذ
        $response = $this->actingAs($teacher, 'sanctum')->postJson('/api/teacher/courses', [
            'title' => 'Advanced Laravel Mastery',
            'description' => 'Deep dive into Laravel features',
            'price' => 150,
            'path_id' => $path->id,
        ]);

        // 3. التأكيد (هذا الجزء هو الذي يزيل الـ Risky)
        $response->assertStatus(201); // التأكد أن الكورس تم إنشاؤه بنجاح
        $this->assertDatabaseHas('courses', [
            'title' => 'Advanced Laravel Mastery',
            'teacher_id' => $teacher->id
        ]);
    }

    #[Test]

    public function student_cannot_access_teacher_routes()
    {
        $userRole = Role::where('name', 'user')->first();
        $student = User::factory()->create(['role_id' => $userRole->id]);

        $this->actingAs($student, 'sanctum');

        $response = $this->getJson('/api/teacher/courses');

        $response->assertStatus(403); // Forbidden
    }
}