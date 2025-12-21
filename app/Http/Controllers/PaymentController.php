<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService) {
        $this->walletService = $walletService;
    }

    /**
     * 1. طلب شحن الرصيد
     */
    public function deposit(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:1000']);
        
        $user = Auth::user();
        
        // التحقق من وجود المحفظة قبل البدء
        if (!$user->wallet) {
            return response()->json(['error' => 'لا توجد محفظة مرتبطة بهذا الحساب'], 404);
        }

        $transaction = $this->walletService->initiateDeposit($user, $request->amount);
        
        return response()->json([
            'success' => true,
            'transaction_id' => $transaction->id,
            'message' => 'تم إنشاء طلب الشحن بنجاح',
            'payment_url' => 'https://shamcash.com/pay/mock_url' 
        ]);
    }

    /**
     * 2. دالة المحاكاة
     */
    public function simulateSuccess($orderId)
    {
        try {
            $this->walletService->completeTransaction(
                'SIMULATED-REF-' . uniqid(), 
                $orderId                    
            );

            return response()->json([
                'success' => true,
                'message' => 'تم محاكاة نجاح الدفع وتحديث المحفظة بنجاح!'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * 3. تحديث معلومات المحفظة (تم تعديله ليحل مشكلة عدم الحفظ)
     */
    public function updateWalletInfo(Request $request)
    {
        $user = Auth::user();
        
        // جلب المحفظة مع التحقق من وجودها
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json(['error' => 'المحفظة غير موجودة، يرجى التواصل مع الدعم'], 404);
        }

        $request->validate([
            'account_number'      => 'nullable|string',
            'old_wallet_password' => 'required_with:new_wallet_password',
            'new_wallet_password' => 'nullable|string|min:4',
        ]);

        // مصفوفة لتخزين البيانات التي سيتم تحديثها
        $updateData = [];

        // تحديث رقم الحساب
        if ($request->filled('account_number')) {
            $updateData['account_number'] = $request->account_number;
        }

        // تحديث كلمة مرور المحفظة
        if ($request->filled('new_wallet_password')) {
            if (!Hash::check($request->old_wallet_password, $wallet->wallet_password)) {
                return response()->json(['error' => 'كلمة مرور المحفظة القديمة غير صحيحة'], 403);
            }
            $updateData['wallet_password'] = Hash::make($request->new_wallet_password);
        }

        // تنفيذ التحديث الفعلي في قاعدة البيانات
        if (!empty($updateData)) {
            $wallet->update($updateData);
        }

        return response()->json([
            'message' => 'تم تحديث معلومات المحفظة بنجاح',
            'wallet'  => $wallet->fresh() // إرسال البيانات المحدثة للتأكد في Postman
        ]);
    }

    /**
     * 4. رابط العودة الحقيقي (Callback)
     */
    public function handleCallback(Request $request)
    {
        try {
            $this->walletService->completeTransaction(
                $request->shamcash_ref, 
                $request->order_id     
            );
            
            return response()->json(['message' => 'تم شحن المحفظة بنجاح']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}