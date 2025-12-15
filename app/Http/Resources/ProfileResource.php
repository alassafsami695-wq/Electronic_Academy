<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray($request)
    {
        return [

            // -------------------------
            // بيانات المستخدم الأساسية
            // -------------------------
            'name'         => optional($this->user)->name,
            'email'        => optional($this->user)->email,

            // -------------------------
            // بيانات البروفايل الأساسي
            // -------------------------
            'photo'        => $this->photo
                                ? asset('storage/' . $this->photo)
                                : null,

            'address'      => $this->address,
            'phone_number' => $this->phone_number,

            // -------------------------
            // بيانات المدرس (تظهر فقط إذا كان Teacher)
            // -------------------------
            'teacher_profile' => $this->user->isTeacher() && $this->user->teacherProfile ? [

                'photo' => $this->user->teacherProfile->photo
                    ? asset('storage/' . $this->user->teacherProfile->photo)
                    : null,

                'facebook_url'  => $this->user->teacherProfile->facebook_url,
                'linkedin_url'  => $this->user->teacherProfile->linkedin_url,
                'instagram_url' => $this->user->teacherProfile->instagram_url,
                'youtube_url'   => $this->user->teacherProfile->youtube_url,
                'github_url'    => $this->user->teacherProfile->github_url,

            ] : null,
        ];
    }
}
