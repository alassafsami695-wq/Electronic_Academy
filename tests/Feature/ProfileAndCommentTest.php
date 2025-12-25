<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Path;
use App\Models\Course;
use PHPUnit\Framework\Attributes\Test;

class ProfileAndCommentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_update_his_profile_name()
    {
        $role = Role::firstOrCreate(['name' => 'user']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $user->profile()->create(['address' => 'initial address']);

        $response = $this->actingAs($user)->postJson('/api/profile/update', [
            'name' => 'New Awesome Name'
        ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function student_cannot_reply_to_comments()
    {
        $studentRole = Role::firstOrCreate(['name' => 'user']);
        $student = User::factory()->create(['role_id' => $studentRole->id]);
        
        $path = Path::create(['title' => 'AI Path']);
        $course = Course::factory()->create(['path_id' => $path->id, 'teacher_id' => $student->id]);
        $lesson = $course->lessons()->create(['title' => 'L1', 'order' => 1]);
        
        $parentComment = $lesson->comments()->create([
            'body' => 'Question?', 
            'user_id' => $student->id
        ]);

        $response = $this->actingAs($student)->postJson('/api/comments', [
            'lesson_id' => $lesson->id,
            'body' => 'I am trying to reply',
            'parent_id' => $parentComment->id
        ]);

        $response->assertStatus(403); 
    }
}