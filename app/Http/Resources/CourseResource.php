<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'title'              => $this->title,
            'description'        => $this->description,
            'photo'              => $this->photo ? asset('storage/' . $this->photo) : null,
            'price'              => $this->price,
            'course_duration'    => $this->course_duration,
            'number_of_students' => $this->number_of_students,
            'rating'             => $this->rating,

            'teacher'            => new UserResource($this->whenLoaded('teacher')),
            'path'               => new PathResource($this->whenLoaded('path')),

            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
        ];
    }
}
