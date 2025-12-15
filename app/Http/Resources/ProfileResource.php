<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'name'         => $this->user->name,
            'email'        => $this->user->email,

            'photo'        => $this->photo ? asset('storage/'.$this->photo) : null,
            'address'      => $this->address,
            'phone_number' => $this->phone_number,

            'teacher_profile' => $this->user->teacherProfile ? [
                'facebook_url' => $this->user->teacherProfile->facebook_url,
                'linkedin_url' => $this->user->teacherProfile->linkedin_url,
                'instagram_url'=> $this->user->teacherProfile->instagram_url,
                'youtube_url'  => $this->user->teacherProfile->youtube_url,
                'github_url'   => $this->user->teacherProfile->github_url,
            ] : null,
        ];
    }
}

