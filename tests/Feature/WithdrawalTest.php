<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use PHPUnit\Framework\Attributes\Test;

class WithdrawalTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function teacher_cannot_withdraw_more_than_balance()
    {
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $teacher = User::factory()->create(['role_id' => $teacherRole->id]);
        $teacher->wallet()->create(['balance' => 50]);

        $response = $this->actingAs($teacher)->postJson('/api/wallet/withdraw', [
            'amount' => 100
        ]);

        $response->assertStatus(400);
        $this->assertEquals(50, $teacher->wallet->fresh()->balance);
    }

    #[Test]
    public function teacher_can_withdraw_successfully_with_enough_balance()
    {
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $teacher = User::factory()->create(['role_id' => $teacherRole->id]);
        $teacher->wallet()->create(['balance' => 200]);

        $response = $this->actingAs($teacher)->postJson('/api/wallet/withdraw', [
            'amount' => 150
        ]);

        $response->assertStatus(200);
        $this->assertEquals(50, $teacher->wallet->fresh()->balance);
    }
}