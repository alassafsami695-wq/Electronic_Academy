<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use App\Http\Resources\ProfileResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * عرض الملف الشخصي مع المحفظة والبيانات المرتبطة
     */
    public function show()
    {
        $user = auth()->user();

        // تأمين وجود سجل البروفايل والمحفظة إذا لم يكونا موجودين
        $this->ensureRelatedModelsExist($user);

        // تحميل العلاقات المطلوبة لضمان ظهور المحفظة (wallet) في الـ Resource
        // نستخدم user.wallet و user.teacherProfile
        return new ProfileResource(
            $user->profile->load(['user.wallet', 'user.teacherProfile'])
        );
    }

    /**
     * تحديث البيانات الشخصية
     */
    public function update(ProfileRequest $request)
    {
        $user = auth()->user();
        
        $this->ensureRelatedModelsExist($user);

        $profile = $user->profile;
        $data = $request->validated();

        // استخدام Transaction لضمان سلامة البيانات عند رفع الصور وتحديث القاعدة
        return DB::transaction(function () use ($request, $profile, $data) {
            // معالجة رفع الصورة
            if ($request->hasFile('photo')) {
                // يمكنك هنا إضافة كود لحذف الصورة القديمة من التخزين إذا أردت
                $data['photo'] = $request->file('photo')->store('profiles', 'public');
            }

            $profile->update($data);

            return new ProfileResource(
                $profile->fresh()->load(['user.wallet', 'user.teacherProfile'])
            );
        });
    }

    /**
     * دالة مساعدة لضمان عدم حدوث خطأ "load() on null"
     * تتأكد من وجود سجلات Profile و Wallet للمستخدم
     */
    private function ensureRelatedModelsExist($user)
    {
        if (!$user->profile) {
            $user->profile()->create([]);
        }

        // حل مشكلة عدم ظهور المحفظة: إنشاؤها إذا كانت مفقودة
        if (!$user->wallet) {
            $user->wallet()->create([
                'balance' => 0,
                'account_number' => 'SHAM-' . rand(100000, 999999),
                'wallet_password' => bcrypt('1234') // كلمة مرور افتراضية
            ]);
        }
    }
}