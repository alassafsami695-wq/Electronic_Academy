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

        // إذا لم يكن لديه teacherProfile → أنشئ واحدًا
        if (!$user->teacherProfile) {
            $user->teacherProfile()->create([]);
        }

        $profile = $user->teacherProfile;

        $profile->update($request->validated());

        return new TeacherProfileResource($profile->fresh());
    }


}

