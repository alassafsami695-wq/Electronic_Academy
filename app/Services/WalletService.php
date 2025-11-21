<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Exception;

class WalletService
{
    /**
     * خصم مبلغ من المحفظة
     */
    public function debit(User $user, float $amount, string $description = null, int $courseId = null)
    {
        $wallet = $user->wallet;

        if (!$wallet) {
            throw new Exception("User wallet not found.");
        }

        if ($wallet->balance < $amount) {
            throw new Exception("Insufficient balance.");
        }

        DB::transaction(function () use ($wallet, $amount, $description, $courseId) {
            $wallet->balance -= $amount;
            $wallet->save();

            Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'amount' => $amount,
                'description' => $description,
                'course_id' => $courseId,
            ]);
        });
    }

    /**
     * إضافة مبلغ للمحفظة
     */
    public function credit(User $user, float $amount, string $description = null, int $courseId = null)
    {
        $wallet = $user->wallet;

        if (!$wallet) {
            throw new Exception("User wallet not found.");
        }

        DB::transaction(function () use ($wallet, $amount, $description, $courseId) {
            $wallet->balance += $amount;
            $wallet->save();

            Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'credit',
                'amount' => $amount,
                'description' => $description,
                'course_id' => $courseId,
            ]);
        });
    }
}
