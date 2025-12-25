<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use App\Models\Path;
use App\Models\Role;
use App\Models\Lesson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAndIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private $studentRole;

    protected function setUp(): void
    {
        parent::setUp();
        $this->studentRole = Role::where('name', 'user')->first() ?? Role::factory()->create(['name' => 'user']);
    }

    /** 1. حماية الحقول الحساسة (Mass Assignment Protection) */
    public function test_user_cannot_promote_themselves_to_admin()
    {
        $user = User::factory()->create([
            'role_id' => $this->studentRole->id,
            'is_super_admin' => false
        ]);

        // محاولة تغيير الحالة عبر تحديث البروفايل
        $this->actingAs($user, 'sanctum')
             ->postJson("/api/profile/update", [
                 'name' => 'Hacker Name',
                 'is_super_admin' => true, // حقل يجب أن يكون محمياً
                 'role_id' => 1 // محاولة تغيير الرول لأدمن
             ]);

        $user->refresh();
        $this->assertFalse($user->is_super_admin);
        $this->assertEquals($this->studentRole->id, $user->role_id);
    }

    /** 2. حماية رصيد المحفظة من التلاعب عبر البروفايل */
    public function test_user_cannot_update_balance_via_profile()
    {
        $user = User::factory()->create(['role_id' => $this->studentRole->id]);
        $wallet = $user->wallet()->create(['balance' => 10.00]);

        $this->actingAs($user, 'sanctum')
             ->postJson("/api/profile/update", [
                 'balance' => 5000.00 // محاولة شحن رصيد وهمية
             ]);

        $this->assertEquals(10.00, $user->wallet->fresh()->balance);
    }

    /** 3. منع الوصول لمحتوى الدروس غير المشتراة */
    public function test_student_cannot_access_unpurchased_lesson_questions()
    {
        $student = User::factory()->create(['role_id' => $this->studentRole->id]);
        $path = Path::factory()->create();
        $course = Course::factory()->create(['path_id' => $path->id]);
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        // الطالب لم يشترك في الكورس (enrolledCourses فارغة)
        $response = $this->actingAs($student, 'sanctum')
                         ->getJson("/api/lessons/{$lesson->id}/questions");

        // يجب أن يرجع النظام 403 أو يوجهه للشراء
        $response->assertStatus(403);
    }
}