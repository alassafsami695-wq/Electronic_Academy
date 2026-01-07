<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Path;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class CourseManagementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]

    public function test_teacher_can_create_course()
    {
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        
        $teacher = User::factory()->create([
            'role_id' => $teacherRole->id,
            'is_verified' => true,
            'status' => 'active'
        ]);

        $path = Path::create(['title' => 'Web Development']);

        $response = $this->actingAs($teacher, 'sanctum')->postJson('/api/teacher/courses', [
            'title' => 'Laravel for Beginners',
            'description' => 'Learn Laravel from scratch',
            'price' => 100,
            'path_id' => $path->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('courses', ['title' => 'Laravel for Beginners']);
    }
}