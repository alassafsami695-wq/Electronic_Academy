<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherProfileResource extends JsonResource
{
 
    public function toArray($request)
    {
        return [
            'facebook_url' => $this->facebook_url,
            'linkedin_url' => $this->linkedin_url,
            'instagram_url'=> $this->instagram_url,
            'youtube_url'  => $this->youtube_url,
            'github_url'   => $this->github_url,
            'photo'        => $this->photo ? asset('storage/' . $this->photo) : null,
            'address'      => $this->address,
            'phone_number' => $this->phone_number,

        ];
    }

}
