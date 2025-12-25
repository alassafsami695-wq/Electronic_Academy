<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Role;
use App\Models\Path;
use PHPUnit\Framework\Attributes\Test;

class WishlistAndAccessTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/wishlist');
        $response->assertStatus(401);
    }

    #[Test]
    public function student_can_toggle_course_in_wishlist()
    {
        $role = Role::firstOrCreate(['name' => 'student']);
        $user = User::factory()->create(['role_id' => $role->id]);
        
        $path = Path::create(['title' => 'Web Development']);
        $course = Course::factory()->create([
            'path_id' => $path->id,
            'title'   => 'Laravel Pro Course'
        ]);

        $response = $this->actingAs($user)
                        ->postJson('/api/wishlist/toggle', [
                            'course_id' => $course->id
                        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('wishlists', [
            'user_id'   => $user->id,
            'course_id' => $course->id
        ]);
    }
}