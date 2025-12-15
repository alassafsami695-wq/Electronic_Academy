<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Models\Profile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show()
    {
        return new ProfileResource(
            auth()->user()->profile->load('user', 'user.teacherProfile')
        );
    }



    public function update(ProfileRequest $request)
    {
        $profile = auth()->user()->profile;

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

