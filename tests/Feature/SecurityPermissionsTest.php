<?php

namespace Tests\Feature;

use App\Models\{User, Course};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SecurityPermissionsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function student_cannot_create_course()
    {
        $student = User::factory()->create(['role_id' => 3]); // طالب

        // محاولة الطالب الدخول لمسارات المدرس لإنشاء كورس
        $response = $this->actingAs($student)->postJson('/api/teacher/courses', [
            'title' => 'دورة غير مصرح بها',
        ]);

        // يجب أن يتم الرفض من قبل Middleware (is.Teacher)
        $response->assertStatus(403);
    }

    #[Test]
    public function guest_cannot_access_protected_routes()
    {
        // محاولة الوصول لرابط الملف الشخصي بدون تسجيل دخول
        $response = $this->getJson('/api/profile'); 

        // يجب أن يعيد 401 (غير مسجل دخول)
        $response->assertStatus(401); 
    }

    #[Test]
    public function teacher_cannot_add_lesson_to_another_teachers_course()
    {
        $teacher1 = User::factory()->create(['role_id' => 2]);
        $teacher2 = User::factory()->create(['role_id' => 2]); // مدرس آخر
        $courseOfTeacher1 = Course::factory()->create(['teacher_id' => $teacher1->id]);

        // محاولة المدرس الثاني إضافة درس لكورس المدرس الأول
        $response = $this->actingAs($teacher2)
                         ->postJson("/api/teacher/courses/{$courseOfTeacher1->id}/lessons", [
                             'title' => 'محاولة اختراق الصلاحيات',
                         ]);

        // يجب أن يعيد 403 لأن المدرس لا يملك هذا الكورس
        $response->assertStatus(403);
    }
}