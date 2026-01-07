<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicCourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'photo'       => $this->photo ? asset('storage/' . $this->photo) : null,
            'price'       => $this->price,
            'course_duration' => $this->course_duration,
            'rating'      => $this->rating,

            // ✅ فقط بيانات أساسية بدون دروس أو progress
            'teacher'     => new UserResource($this->whenLoaded('teacher')),
            'path'        => $this->path ? [
                'id'    => $this->path->id,
                'title' => $this->path->title,
            ] : null,

            'created_at'  => $this->created_at,
        ];
    }
}
