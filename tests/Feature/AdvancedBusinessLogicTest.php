<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Role;
use App\Models\Path;
use App\Models\Comment;
use App\Models\Lesson;
use PHPUnit\Framework\Attributes\Test;

class AdvancedBusinessLogicTest extends TestCase
{
    use RefreshDatabase;

    private $studentRole, $teacherRole, $adminRole;

    protected function setUp(): void
    {
        parent::setUp();
        // إعداد الأدوار بشكل افتراضي لجميع الاختبارات
        $this->studentRole = Role::firstOrCreate(['name' => 'user']);
        $this->teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $this->adminRole   = Role::firstOrCreate(['name' => 'admin']);
    }

    #[Test]
    public function student_cannot_purchase_same_course_twice()
    {
        $student = User::factory()->create(['role_id' => $this->studentRole->id]);
        $student->wallet()->create(['balance' => 1000]);

        $teacher = User::factory()->create(['role_id' => $this->teacherRole->id]);
        $path = Path::factory()->create();
        $course = Course::factory()->create(['price' => 100, 'teacher_id' => $teacher->id, 'path_id' => $path->id]);

        $this->actingAs($student)->postJson("/api/courses/{$course->id}/purchase");
        $response = $this->actingAs($student)->postJson("/api/courses/{$course->id}/purchase");

        $response->assertStatus(400);
        $response->assertJsonFragment(['message' => 'أنت مشترك بالفعل في هذا الكورس']);
    }

    #[Test]
    public function teacher_cannot_update_others_course()
    {
        $teacherA = User::factory()->create(['role_id' => $this->teacherRole->id]);
        $teacherB = User::factory()->create(['role_id' => $this->teacherRole->id]);
        
        $path = Path::factory()->create();
        $courseOfA = Course::factory()->create(['teacher_id' => $teacherA->id, 'path_id' => $path->id]);

        $response = $this->actingAs($teacherB)->postJson("/api/teacher/courses/{$courseOfA->id}/update", [
            'title' => 'Hacked Title'
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function student_cannot_reply_to_comments_logic()
    {
        $student = User::factory()->create(['role_id' => $this->studentRole->id]);
        $path = Path::factory()->create();
        $course = Course::factory()->create(['teacher_id' => $student->id, 'path_id' => $path->id]);
        $lesson = $course->lessons()->create(['title' => 'L1', 'order' => 1]);
        
        $comment = Comment::create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'body' => 'Original Comment'
        ]);

        $response = $this->actingAs($student)->postJson("/api/comments", [
            'lesson_id' => $lesson->id,
            'body' => 'I am trying to reply as student',
            'parent_id' => $comment->id
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function user_cannot_withdraw_exceeding_balance()
    {
        $teacher = User::factory()->create(['role_id' => $this->teacherRole->id]);
        $teacher->wallet()->create(['balance' => 100]);

        $response = $this->actingAs($teacher)->postJson("/api/wallet/withdraw", [
            'amount' => 500
        ]);

        $response->assertStatus(400);
        $response->assertJsonFragment(['message' => 'رصيدك الحالي غير كافٍ لإتمام عملية السحب']);
    }

    #[Test]
    public function suspended_user_cannot_login()
    {
        $user = User::factory()->create([
            'email' => 'suspended@test.com',
            'password' => bcrypt('password123'),
            'status' => 'suspended',
            'role_id' => $this->studentRole->id,
            'is_verified' => true
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'suspended@test.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(403);
        $response->assertJsonFragment(['message' => 'عذراً، حسابك معلق حالياً، يرجى التواصل مع الإدارة']);
    }

    #[Test]
    public function generate_questions_validation()
    {
        $path = Path::factory()->create();
        
        // نضمن أن المدرس نشط، مفعل، وله الرتبة الصحيحة لتجاوز الـ Middleware
        $teacher = User::factory()->create([
            'role_id' => $this->teacherRole->id,
            'status' => 'active',
            'email_verified_at' => now()
        ]);

        $course = Course::factory()->create([
            'teacher_id' => $teacher->id,
            'path_id' => $path->id
        ]);

        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        $response = $this->actingAs($teacher, 'sanctum')
                         ->postJson("/api/teacher/lessons/{$lesson->id}/questions/generate", [
                             'text' => '' // نص فارغ لتحفيز خطأ الـ Validation 422
                         ]);

        $response->assertStatus(422); 
    }
}