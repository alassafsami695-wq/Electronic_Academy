<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * عرض الملف الشخصي الموحد (أدمن، مدرس، طالب)
     */
    public function show()
    {
        $user = auth()->user();

        // تأمين وجود سجلات البروفايل، المحفظة، وبروفايل المدرس إذا لزم الأمر
        $this->ensureRelatedModelsExist($user);

        // تحميل العلاقات لضمان ظهور كافة البيانات في الـ Resource
        return new ProfileResource(
            $user->profile->load(['user.wallet', 'user.teacherProfile'])
        );
    }

    /**
     * تحديث البيانات الشخصية والبيانات الإضافية بناءً على الرتبة
     */
    public function update(ProfileRequest $request)
    {
        $user = auth()->user();
        
        $this->ensureRelatedModelsExist($user);

        $profile = $user->profile;
        $data = $request->validated();

        return DB::transaction(function () use ($request, $user, $profile, $data) {
            
            // 1. تحديث الاسم في جدول المستخدمين إذا تم إرساله
            if ($request->has('name')) {
                $user->update(['name' => $data['name']]);
            }

            // 2. معالجة رفع الصورة الشخصية
            if ($request->hasFile('photo')) {
                // حذف الصورة القديمة إذا وجدت
                if ($profile->photo) {
                    Storage::disk('public')->delete($profile->photo);
                }
                $data['photo'] = $request->file('photo')->store('profiles', 'public');
            }

            // 3. تحديث بيانات البروفايل الأساسية (العنوان، الهاتف، تاريخ الميلاد، الصورة)
            $profile->update($request->only(['address', 'phone_number', 'birth_date', 'photo']));

            // 4. إذا كان المستخدم مدرساً، يتم تحديث روابط التواصل الاجتماعي
            if ($user->role === 'teacher' && $user->teacherProfile) {
                $user->teacherProfile->update($request->only([
                    'facebook_url', 
                    'linkedin_url', 
                    'instagram_url', 
                    'youtube_url', 
                    'github_url'
                ]));
            }

            return new ProfileResource(
                $profile->fresh()->load(['user.wallet', 'user.teacherProfile'])
            );
        });
    }

    /**
     * عرض بروفايل مدرس معين للعامة (أو لأي مستخدم)
     */
    public function publicShow($id)
    {
        $user = User::where('id', $id)
            ->where('role', 'teacher') // التأكد أنه مدرس
            ->with(['profile', 'teacherProfile'])
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data'   => [
                'name'          => $user->name,
                'email'         => $user->email,
                'photo'         => $user->profile->photo ? asset('storage/' . $user->profile->photo) : null,
                'address'       => $user->profile->address,
                'phone_number'  => $user->profile->phone_number,
                'birth_date'    => $user->profile->birth_date,
                'teacher_info'  => $user->teacherProfile 
            ]
        ]);
    }

    /**
     * دالة مساعدة لضمان وجود السجلات المرتبطة وتجنب أخطاء Null
     */
    private function ensureRelatedModelsExist($user)
    {
        // إنشاء البروفايل الأساسي
        if (!$user->profile) {
            $user->profile()->create([]);
        }

        // إنشاء المحفظة
        if (!$user->wallet) {
            $user->wallet()->create([
                'balance' => 0,
                'account_number' => 'SHAM-' . rand(100000, 999999),
                'wallet_password' => bcrypt('1234')
            ]);
        }

        // إنشاء بروفايل المدرس إذا كان المستخدم مسجلاً كمدرس
        if ($user->role === 'teacher' && !$user->teacherProfile) {
            $user->teacherProfile()->create([]);
        }
    }
}