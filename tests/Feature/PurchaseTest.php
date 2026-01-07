<?php

namespace Tests\Feature;

use App\Models\{User, Course, Wallet};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    #[Test]

    public function test_student_can_purchase_single_course()
    {
        $student = User::factory()->create();
        $course = Course::factory()->create(['price' => 200]);
        Wallet::create(['user_id' => $student->id, 'balance' => 500]);

        $this->actingAs($student, 'sanctum');

        $response = $this->postJson("/api/courses/{$course->id}/purchase");

        $response->assertStatus(200);
        $this->assertDatabaseHas('course_user', [
            'user_id' => $student->id,
            'course_id' => $course->id
        ]);
    }
}