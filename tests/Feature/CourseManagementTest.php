<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Role;
use App\Models\Path;
use PHPUnit\Framework\Attributes\Test;

class CourseManagementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function teacher_can_create_lesson_for_his_course()
    {
        // 1. إعداد البيانات الأساسية (الدور والمدرس)
        // نستخدم lowercase 'teacher' لضمان مطابقة الـ Middleware
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        
        $teacher = User::factory()->create([
            'role_id' => $teacherRole->id,
            'status'  => 'active', // ضمان أن المدرس غير محظور
        ]);

        // 2. إعداد الكورس
        $path = Path::firstOrCreate(['title' => 'Mobile Apps']);
        
        // ربط الكورس بالمدرس ضروري جداً لتخطي الـ Policy
        $course = Course::factory()->create([
            'teacher_id' => $teacher->id, 
            'path_id'    => $path->id,
            'title'      => 'New Course'
        ]);

        // 3. تنفيذ الطلب
        $response = $this->actingAs($teacher)
                         ->postJson("/api/teacher/courses/{$course->id}/lessons", [
                             'title'   => 'First Lesson',
                             'order'   => 1,
                             'content' => 'Sample Content'
                         ]);

        // 4. التحقق من النتيجة
        // إذا استمر الـ 403، فالمشكلة في الـ Middleware بالـ Route
        $response->assertStatus(201);
        
        $this->assertDatabaseHas('lessons', [
            'course_id' => $course->id,
            'title'     => 'First Lesson'
        ]);
    }
}