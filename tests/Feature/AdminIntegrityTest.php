<?php

namespace Tests\Feature;

use App\Models\{User, Role};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AdminIntegrityTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_suspend_user_account()
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create([
            'role_id' => 1, // الأدمن حسب الـ Middleware الخاص بك
            'is_super_admin' => true,
            'status' => 'active']);
            $user = User::factory()->create(['status' => 'active']);

        $this->actingAs($admin, 'sanctum');

        $response = $this->postJson("/api/admin/users/{$user->id}/toggle-status");

        $response->assertStatus(200);
        $this->assertEquals('suspended', $user->fresh()->status);
    }
}