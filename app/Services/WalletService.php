<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Exception;

class WalletService
{
    // الخطوة 1: تسجيل طلب شحن محفظة
    public function initiateDeposit(User $user, float $amount, string $description = null)
    {
        return Transaction::create([
            'wallet_id' => $user->wallet->id,
            'amount' => $amount,
            'type' => 'credit',
            'status' => 'pending',
            'description' => $description ?? "شحن محفظة عبر شام كاش",
        ]);
    }

    // الخطوة 2: تأكيد العملية وتحديث الرصيد
    public function completeTransaction(string $referenceId, $transactionId)
    {
        $transaction = Transaction::with('wallet')->findOrFail($transactionId);

        if ($transaction->status !== 'pending') {
            throw new Exception("العملية معالجة مسبقاً.");
        }

        DB::transaction(function () use ($transaction, $referenceId) {
            $wallet = $transaction->wallet;

            if (!$wallet) {
                throw new Exception("المحفظة المرتبطة بهذه العملية غير موجودة.");
            }

            $wallet->balance += $transaction->amount;
            $wallet->save();

            $transaction->update([
                'status' => 'completed',
                'reference_id' => $referenceId
            ]);
        });

        return true;
    }

    // تم تعديل الاسم هنا ليصبح debit ليتوافق مع الـ Controller
    public function debit(User $user, float $amount, string $description)
    {
        $wallet = $user->wallet;

        if (!$wallet) {
            throw new Exception("لا توجد محفظة لهذا المستخدم.");
        }

        if ($wallet->balance < $amount) {
            throw new Exception("رصيدك غير كافٍ لإتمام عملية الشراء.");
        }

        return DB::transaction(function () use ($wallet, $amount, $description) {
            $wallet->balance -= $amount;
            $wallet->save();

            return Transaction::create([
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'type' => 'debit',
                'status' => 'completed',
                'description' => $description,
            ]);
        });
    }
}