<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use App\Models\Path;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CoursePurchasedNotification; // افترضنا وجود هذا الاسم
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private $studentRole, $teacherRole;

    protected function setUp(): void
    {
        parent::setUp();
        $this->studentRole = Role::where('name', 'user')->first() ?? Role::factory()->create(['name' => 'user']);
        $this->teacherRole = Role::where('name', 'teacher')->first() ?? Role::factory()->create(['name' => 'teacher']);
    }

    /** 1. اختبار إرسال إشعار للمدرس عند شراء كورس */
    public function test_teacher_receives_notification_when_course_is_purchased()
    {
        // تزييف الإشعارات لمنع الإرسال الحقيقي
        Notification::fake();

        $teacher = User::factory()->create(['role_id' => $this->teacherRole->id]);
        $student = User::factory()->create(['role_id' => $this->studentRole->id]);
        $student->wallet()->create(['balance' => 1000]);

        $path = Path::factory()->create();
        $course = Course::factory()->create([
            'teacher_id' => $teacher->id,
            'price' => 100,
            'path_id' => $path->id
        ]);

        // عملية الشراء
        $this->actingAs($student, 'sanctum')
             ->postJson("/api/courses/{$course->id}/purchase");

        // التأكد من أن الإشعار أُرسل للمدرس تحديداً
        Notification::assertSentTo(
            $teacher, 
            function (CoursePurchasedNotification $notification) use ($course) {
                return $notification->course->id === $course->id;
            }
        );
    }
}