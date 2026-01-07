<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class SecurityAndIntegrityTest extends TestCase
{
    use RefreshDatabase;

    #[Test]

    public function test_admin_can_suspend_user()
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        
        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'is_verified' => true,
            'is_super_admin' => true
        ]);
        
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($admin, 'sanctum')->postJson("/api/admin/users/{$user->id}/toggle-status");

        $response->assertStatus(200);
        $this->assertEquals('suspended', $user->fresh()->status);
    }
}