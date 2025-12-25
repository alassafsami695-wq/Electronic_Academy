<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Role;
use App\Models\Path;
use PHPUnit\Framework\Attributes\Test; 

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_profit_distribution_after_purchase()
    {
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $studentRole = Role::firstOrCreate(['name' => 'student']);
        $adminRole   = Role::firstOrCreate(['name' => 'admin']);
        
        $path = Path::firstOrCreate(['title' => 'Programming Path']); 

        $teacher = User::factory()->create(['role_id' => $teacherRole->id]);
        $teacher->wallet()->create(['balance' => 0]);

        $admin = User::factory()->create([
            'is_super_admin' => true,
            'role_id' => $adminRole->id
        ]);
        $admin->wallet()->create(['balance' => 0]);

        $course = Course::factory()->create([
            'teacher_id' => $teacher->id, 
            'path_id'    => $path->id, 
            'price'      => 100
        ]);

        $student = User::factory()->create(['role_id' => $studentRole->id]);
        $student->wallet()->create(['balance' => 500]);

        $response = $this->actingAs($student)
                         ->postJson("/api/courses/{$course->id}/purchase");

        $response->assertSuccessful();

        $this->assertEquals(80, $teacher->wallet->fresh()->balance); 
        $this->assertEquals(20, $admin->wallet->fresh()->balance);
        $this->assertEquals(400, $student->wallet->fresh()->balance);
    }
}