<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherProfileRequest;
use App\Http\Resources\TeacherProfileResource;
use App\Models\User;
use Illuminate\Http\Request;

class TeacherProfileController extends Controller
{
    
     // عرض بروفايل المدرس (للمدرس نفسه فقط)
     
    public function show()
    {
        $user = auth()->user();

        // إنشاء teacherProfile إذا لم يكن موجود
        if (!$user->teacherProfile) {
            $user->teacherProfile()->create([]);
        }

        return new TeacherProfileResource($user->teacherProfile);
    }

    
     // تحديث بروفايل المدرس (للمدرس نفسه فقط)
     
    public function update(TeacherProfileRequest $request)
    {
        $user = auth()->user();

        // إنشاء teacherProfile إذا لم يكن موجود
        if (!$user->teacherProfile) {
            $user->teacherProfile()->create([]);
        }

        $data = $request->validated();

        // رفع الصورة إذا كانت موجودة
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('teacher_profiles', 'public');
        }

        $user->teacherProfile->update($data);

        return new TeacherProfileResource($user->teacherProfile->fresh());
    }

    
     // --------------عرض بروفايل أي مدرس (لأي مستخدم)---------------------
    
    public function publicShow($id)
    {
        $user = User::where('id', $id)
            ->whereHas('teacherProfile')
            ->with(['profile', 'teacherProfile'])
            ->firstOrFail();

        return response()->json([
            'name'         => $user->name,
            'email'        => $user->email,

            'photo'        => $user->teacherProfile->photo
                                ? asset('storage/' . $user->teacherProfile->photo)
                                : null,

            'address'      => $user->profile->address ?? null,
            'phone_number' => $user->profile->phone_number ?? null,

            'facebook_url' => $user->teacherProfile->facebook_url,
            'linkedin_url' => $user->teacherProfile->linkedin_url,
            'instagram_url'=> $user->teacherProfile->instagram_url,
            'youtube_url'  => $user->teacherProfile->youtube_url,
            'github_url'   => $user->teacherProfile->github_url,
        ]);
    }
}
