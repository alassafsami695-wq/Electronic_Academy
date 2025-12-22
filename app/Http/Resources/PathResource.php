<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PathResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            // إضافة صورة المسار هنا
            'photo'       => $this->photo ? asset('storage/' . $this->photo) : null,

            // استخدام CourseResource لضمان ظهور كافة حقول الكورس (is_enrolled, rating, الخ)
            'courses'     => CourseResource::collection($this->whenLoaded('courses', $this->courses)),
        ];
    }
}