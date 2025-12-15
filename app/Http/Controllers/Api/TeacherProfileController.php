<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherProfileRequest;
use App\Http\Resources\TeacherProfileResource;
use Illuminate\Http\Request;

class TeacherProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();

        // إذا لم يكن لديه teacherProfile → أنشئ واحدًا
        if (!$user->teacherProfile) {
            $user->teacherProfile()->create([]);
        }

        return new TeacherProfileResource($user->teacherProfile);
    }


    
    public function update(TeacherProfileRequest $request)
    {
        $user = auth()->user();

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

        public function publicShow($id)
    {
        $user = \App\Models\User::where('id', $id)
            ->whereHas('teacherProfile')
            ->with(['profile', 'teacherProfile'])
            ->firstOrFail();

        return response()->json([
            'name'           => $user->name,
            'email'          => $user->email,
            'photo'          => $user->teacherProfile->photo ? asset('storage/' . $user->teacherProfile->photo) : null,
            'address'        => $user->profile->address ?? null,
            'phone_number'   => $user->profile->phone_number ?? null,
            'facebook_url'   => $user->teacherProfile->facebook_url,
            'linkedin_url'   => $user->teacherProfile->linkedin_url,
            'instagram_url'  => $user->teacherProfile->instagram_url,
            'youtube_url'    => $user->teacherProfile->youtube_url,
            'github_url'     => $user->teacherProfile->github_url,
        ]);
    }




}

