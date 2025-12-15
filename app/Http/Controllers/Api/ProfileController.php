<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use App\Http\Resources\ProfileResource;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();

        // إذا لم يكن للمستخدم بروفايل → أنشئ واحدًا
        if (!$user->profile) {
            $user->profile()->create([]);
        }

        return new ProfileResource(
            $user->profile->load('user', 'user.teacherProfile')
        );
    }

    public function update(ProfileRequest $request)
    {
        $user = auth()->user();

        if (!$user->profile) {
            $user->profile()->create([]);
        }

        $profile = $user->profile;
        $data = $request->validated();

        // رفع الصورة
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('profiles', 'public');
        }

        $profile->update($data);

        return new ProfileResource(
            $profile->fresh()->load('user', 'user.teacherProfile')
        );
    }
}
