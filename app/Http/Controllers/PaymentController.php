<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use App\Models\Course;
use App\Models\User;
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
     * 1. شراء كورس وتقسيم الأرباح
     * (جديد: يخصم من الطالب، يعطي الأستاذ 80% والسوبر أدمن 20%)
     */
    public function purchaseCourse(Request $request, $courseId)
    {
        $student = Auth::user();
        $course = Course::with('teacher.wallet')->findOrFail($courseId);

        // 1. التحقق من الرصيد
        if (!$student->wallet || $student->wallet->balance < $course->price) {
            return response()->json(['error' => 'رصيدك غير كافٍ لشراء هذا الكورس'], 400);
        }

        // 2. التحقق مما إذا كان الطالب قد اشترى الكورس مسبقاً
        if ($student->enrolledCourses()->where('course_id', $courseId)->exists()) {
            return response()->json(['message' => 'أنت مشترك في هذا الكورس بالفعل'], 400);
        }

        try {
            DB::transaction(function () use ($student, $course) {
                // أ. خصم المبلغ من محفظة الطالب
                $student->wallet->decrement('balance', $course->price);

                // ب. حساب تقسيم الأرباح (مثال: 80% للأستاذ، 20% للمنصة)
                $teacherShare = $course->price * 0.80;
                $adminShare   = $course->price * 0.20;

                // ج. تحويل حصة الأستاذ إلى محفظته
                if ($course->teacher && $course->teacher->wallet) {
                    $course->teacher->wallet->increment('balance', $teacherShare);
                }

                // د. تحويل حصة المنصة (السوبر أدمن)
                $superAdmin = User::where('is_super_admin', true)->first();
                if ($superAdmin && $superAdmin->wallet) {
                    $superAdmin->wallet->increment('balance', $adminShare);
                }

                // هـ. تسجيل الطالب في الكورس
                $student->enrolledCourses()->attach($course->id);
            });

            return response()->json([
                'success' => true,
                'message' => 'تم شراء الكورس بنجاح وتوزيع الأرباح'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'حدث خطأ أثناء عملية الشراء: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 2. طلب شحن الرصيد
     */
    public function deposit(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:1000']);
        
        $user = Auth::user();
        
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
     * 3. دالة المحاكاة لنجاح الشحن
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
     * 4. تحديث معلومات المحفظة
     */
    public function updateWalletInfo(Request $request)
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json(['error' => 'المحفظة غير موجودة'], 404);
        }

        $request->validate([
            'account_number'      => 'nullable|string',
            'old_wallet_password' => 'required_with:new_wallet_password',
            'new_wallet_password' => 'nullable|string|min:4',
        ]);

        $updateData = [];

        if ($request->filled('account_number')) {
            $updateData['account_number'] = $request->account_number;
        }

        if ($request->filled('new_wallet_password')) {
            if (!Hash::check($request->old_wallet_password, $wallet->wallet_password)) {
                return response()->json(['error' => 'كلمة مرور المحفظة القديمة غير صحيحة'], 403);
            }
            $updateData['wallet_password'] = Hash::make($request->new_wallet_password);
        }

        if (!empty($updateData)) {
            $wallet->update($updateData);
        }

        return response()->json([
            'message' => 'تم تحديث معلومات المحفظة بنجاح',
            'wallet'  => $wallet->fresh()
        ]);
    }

    /**
     * 5. Callback (رابط العودة)
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