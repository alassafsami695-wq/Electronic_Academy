<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
class PaymentAndWalletTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_deposit_money_to_wallet()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 0]);

        $response = $this->actingAs($user)->postJson('/api/deposit', [
            'amount' => 500
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['transaction_id', 'payment_url']);
    }

    #[Test]
    public function user_cannot_withdraw_more_than_balance()
    {
        $user = User::factory()->create();
        // تم تصحيح الخطأ من $user.id إلى $user->id
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 100]);

        // تأكد من وجود مسار الـ withdraw في api.php
        $response = $this->actingAs($user)->postJson('/api/wallet/withdraw', [
            'amount' => 500
        ]);

        // إذا كان المنطق يمنع السحب، نتوقع خطأ 400 أو 422
        $response->assertStatus(400); 
    }
}