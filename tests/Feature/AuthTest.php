<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class AuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]

    public function test_student_can_register()
    {
        Role::firstOrCreate(['name' => 'user']); // الاسم في الـ Controller هو 'user' للطالب

        $response = $this->postJson('/api/register/student', [
            'name' => 'Student Test',
            'email' => 'student@test.com',
            'password' => 'password123',
            'wallet_password' => '1234',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'student@test.com']);
    }

    #[Test]

    public function test_user_can_login()
    {
        $role = Role::firstOrCreate(['name' => 'user']);
        
        $user = User::factory()->create([
            'email' => 'login_success@test.com',
            'password' => bcrypt('password123'),
            'role_id' => $role->id,
            'status' => 'active',
            'is_verified' => true, // هذا هو المفتاح لحل الـ 403 في كودك
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login_success@test.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['access_token', 'user']);
    }
}