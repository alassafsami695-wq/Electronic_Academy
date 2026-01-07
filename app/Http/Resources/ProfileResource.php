<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray($request)
    {
        $user = $this->user;

        return [
            'id'           => $user->id,
            'name'         => $user->name,
            'email'        => $user->email,
            'role'         => $user->role,
          
            'photo'        => $this->photo, 
            
            'address'      => $this->address,
            'phone_number' => $this->phone_number,
            'birth_date'   => $this->birth_date,
            
            // بيانات المحفظة
            'wallet'       => $user->wallet ? [
                'balance'        => $user->wallet->balance,
                'account_number' => $user->wallet->account_number,
            ] : null,

            'teacher_details' => ($user->role === 'teacher' && $user->teacherProfile) ? [
                'facebook_url'  => $user->teacherProfile->facebook_url,
                'linkedin_url'  => $user->teacherProfile->linkedin_url,
                'instagram_url' => $user->teacherProfile->instagram_url,
                'youtube_url'   => $user->teacherProfile->youtube_url,
                'github_url'    => $user->teacherProfile->github_url,
            ] : null,
            
            'is_admin' => $user->role === 'admin',
        ];
    }
}