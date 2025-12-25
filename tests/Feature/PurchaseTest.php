<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Course;

class PurchaseTest extends TestCase
{
    use RefreshDatabase; // لتفريغ قاعدة بيانات الاختبار بعد كل تجربة

    /** @test */
    public function test_profit_distribution_after_purchase()
    {
        // 1. إنشاء أستاذ ومحفظة
        $teacher = User::factory()->create(['role' => 'teacher']);
        $teacher->wallet()->create(['balance' => 0]);

        // 2. إنشاء سوبر أدمن ومحفظة
        $admin = User::factory()->create(['is_super_admin' => true]);
        $admin->wallet()->create(['balance' => 0]);

        // 3. إنشاء كورس سعره 100
        $course = Course::factory()->create(['teacher_id' => $teacher->id, 'price' => 100]);

        // 4. طالب يقوم بالشراء
        $student = User::factory()->create(['role' => 'student']);
        $student->wallet()->create(['balance' => 1000]); // تأكد من وجود رصيد للطالب

        $this->actingAs($student)
             ->postJson("/api/courses/{$course->id}/purchase");

        // 5. التحقق من النتائج
        $this->assertEquals(80, $teacher->wallet->fresh()->balance); // الأستاذ أخذ 80%
        $this->assertEquals(20, $admin->wallet->fresh()->balance);   // الأدمن أخذ 20%
    }
}