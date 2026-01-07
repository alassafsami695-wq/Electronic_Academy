<?php

namespace Tests\Feature;

use App\Models\{User, Wallet};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class PaymentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]

   public function test_user_can_deposit_to_wallet() {
    $user = User::factory()->create();
    $wallet = \App\Models\Wallet::factory()->create(['user_id' => $user->id, 'balance' => 100]);
    $this->actingAs($user, 'sanctum');

    $response = $this->postJson('/api/deposit', ['amount' => 500]);
    
    // استخراج رقم المعاملة من الرد (تأكد من اسم الحقل في Controller الخاص بك)
    $transactionId = $response->json('transaction_id'); 

    // استدعاء رابط النجاح لمحاكاة الدفع الفعلي
    $this->getJson("/api/simulate-payment/{$transactionId}"); 

    $this->assertEquals(600, $user->wallet->fresh()->balance);
}

    #[Test]


    public function test_withdrawal_request_security()
    {
        $user = \App\Models\User::factory()->create();
        \App\Models\Wallet::factory()->create(['user_id' => $user->id, 'balance' => 100]);

        // يجب استخدام actingAs لأن المسار محمي بـ Sanctum
        $response = $this->actingAs($user)->postJson('/api/wallet/withdraw', [
            'amount' => 1000 // مبلغ أكبر من الرصيد
        ]);

        // نتوقع 422 (Validation Error) أو 400 (Bad Request) حسب كود الـ Controller لديك
        $this->assertTrue(in_array($response->getStatusCode(), [400, 422]));
    }
}