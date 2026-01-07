<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = auth('sanctum')->user();
        $isEnrolled = $user ? $this->enrolledUsers()->where('user_id', $user->id)->exists() : false;

        return [
            'id'                 => $this->id,
            'title'              => $this->title,
            'description'        => $this->description,
            
            // ✅ التعديل الجذري: نستخدم photo_url من الموديل مباشرة
            // سيقوم تلقائياً بإرجاع الرابط بدون كلمة storage
            'photo'              => $this->photo_url, 

            'price'              => $this->price,
            'course_duration'    => $this->course_duration,
            'number_of_students' => $this->number_of_students,
            'rating'             => $this->rating,
            'is_enrolled'        => $isEnrolled,
            'progress'           => $isEnrolled ? $this->progress_percentage : 0,

            'teacher'            => new UserResource($this->whenLoaded('teacher')),
            'path'               => $this->path ? [
                'id'    => $this->path->id,
                'title' => $this->path->title,
            ] : null,

            'lessons'            => ($isEnrolled && $this->relationLoaded('lessons'))
                ? LessonResource::collection($this->lessons)
                : null,

            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
        ];
    }
}