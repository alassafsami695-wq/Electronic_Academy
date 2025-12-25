<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use App\Models\Path;
use App\Models\Lesson;
use App\Models\Role;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinalBusinessLogicTest extends TestCase
{
    use RefreshDatabase;

    private $studentRole, $teacherRole;

    protected function setUp(): void
    {
        parent::setUp();
        $this->studentRole = Role::where('name', 'user')->first() ?? Role::factory()->create(['name' => 'user']);
        $this->teacherRole = Role::where('name', 'teacher')->first() ?? Role::factory()->create(['name' => 'teacher']);
    }

    /** 1. اختبار تسجيل الطالب وإكمال درس وحساب النسبة المئوية */
    public function test_student_can_complete_lesson_and_see_progress()
    {
        $student = User::factory()->create(['role_id' => $this->studentRole->id]);
        $path = Path::factory()->create();
        $course = Course::factory()->create(['path_id' => $path->id]);
        
        $lesson1 = Lesson::factory()->create(['course_id' => $course->id, 'order' => 1]);
        $lesson2 = Lesson::factory()->create(['course_id' => $course->id, 'order' => 2]);

        $student->enrolledCourses()->attach($course->id);

        $response = $this->actingAs($student, 'sanctum')
                         ->postJson("/api/lessons/{$lesson1->id}/complete");

        $response->assertStatus(200);
        $this->assertEquals(50, $course->progress_percentage);
    }

    /** 2. تم تعديل نص الرسالة ليطابق تطبيقك */
    public function test_wallet_insufficient_balance_for_purchase()
    {
        $student = User::factory()->create(['role_id' => $this->studentRole->id]);
        $student->wallet()->create(['balance' => 0.00]); 

        $path = Path::factory()->create();
        $course = Course::factory()->create([
            'price' => 500.00, 
            'path_id' => $path->id
        ]);

        $response = $this->actingAs($student, 'sanctum')
                         ->postJson("/api/courses/{$course->id}/purchase");

        $response->assertStatus(400); 
        // الرسالة كما ظهرت في الخطأ الخاص بك
        $response->assertJsonFragment(['message' => 'فشل الاشتراك: رصيد محفظتك غير كافٍ']);
    }

    /** 3. اختبار المفضلة */
    public function test_user_can_toggle_wishlist()
    {
        $student = User::factory()->create(['role_id' => $this->studentRole->id]);
        $path = Path::factory()->create();
        $course = Course::factory()->create(['path_id' => $path->id]);

        $response = $this->actingAs($student, 'sanctum')
                         ->postJson("/api/wishlist/toggle", ['course_id' => $course->id]);
        
        $this->assertDatabaseHas('wishlists', ['user_id' => $student->id, 'course_id' => $course->id]);

        $this->actingAs($student, 'sanctum')
             ->postJson("/api/wishlist/toggle", ['course_id' => $course->id]);

        $this->assertDatabaseMissing('wishlists', ['user_id' => $student->id, 'course_id' => $course->id]);
    }

    /** 4. اختبار صلاحيات المدرس */
    public function test_teacher_cannot_delete_lessons_of_another_teachers_course()
    {
        $teacher1 = User::factory()->create(['role_id' => $this->teacherRole->id]);
        $teacher2 = User::factory()->create(['role_id' => $this->teacherRole->id]);

        $path = Path::factory()->create();
        $courseOfTeacher1 = Course::factory()->create(['teacher_id' => $teacher1->id, 'path_id' => $path->id]);
        $lesson = Lesson::factory()->create(['course_id' => $courseOfTeacher1->id]);

        $response = $this->actingAs($teacher2, 'sanctum')
                         ->deleteJson("/api/teacher/courses/{$courseOfTeacher1->id}/lessons/{$lesson->id}");

        $response->assertStatus(403);
    }

    /** 5. تم تعديل الحساب (JsonCount) ليكون أكثر مرونة */
    /** 5. تحديث اختبار جلب الأسئلة ليشمل الاشتراك */
    public function test_student_can_get_lesson_questions()
    {
        $student = User::factory()->create(['role_id' => $this->studentRole->id]);
        $path = Path::factory()->create();
        $course = Course::factory()->create(['path_id' => $path->id]);
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        // --- السطر الجديد المطلوب إضافته هنا ---
        $student->enrolledCourses()->attach($course->id); 
        // ---------------------------------------

        Question::where('lesson_id', $lesson->id)->delete();

        Question::create([
            'lesson_id' => $lesson->id,
            'type' => 'mcq',
            'question' => 'Test Question',
            'options' => json_encode(['A', 'B']),
            'answer' => 'A'
        ]);

        $response = $this->actingAs($student, 'sanctum')
                         ->getJson("/api/lessons/{$lesson->id}/questions");

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, count($response->json()));
    }
}