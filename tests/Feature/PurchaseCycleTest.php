<?php

namespace Tests\Feature;

use App\Models\{User, Course, Wallet};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class PurchaseCycleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]

    public function student_can_purchase_course_if_has_balance()
    {
        $student = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $student->id, 'balance' => 1000]);
        $course = Course::factory()->create(['price' => 200]);

        $this->actingAs($student, 'sanctum');

        $response = $this->postJson("/api/courses/{$course->id}/purchase");

        $response->assertStatus(200);
        $this->assertDatabaseHas('course_user', [
            'user_id' => $student->id,
            'course_id' => $course->id
        ]);
        
        // التحقق من خصم المبلغ
        $this->assertEquals(800, $student->wallet->fresh()->balance);
    }
}