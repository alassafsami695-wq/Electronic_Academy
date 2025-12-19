<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = auth()->user();
        $isEnrolled = $user ? $user->enrolledCourses->contains($this->id) : false;

        return [
            'id'                 => $this->id,
            'title'              => $this->title,
            'description'        => $this->description,
            'photo'              => $this->photo ? asset('storage/' . $this->photo) : null,
            'price'              => $this->price,
            'course_duration'    => $this->course_duration,
            'number_of_students' => $this->number_of_students,
            'rating'             => $this->rating,
            'is_enrolled'        => $isEnrolled,

            // ✅ الآن سيظهر progress إذا تم حسابه في الـ Controller
            'progress'           => property_exists($this, 'progress') ? $this->progress : null,

            'teacher'            => new UserResource($this->whenLoaded('teacher')),
            'path'               => $this->path ? [
                'id'    => $this->path->id,
                'title' => $this->path->title,
            ] : null,

            'lessons'            => $this->relationLoaded('lessons')
                ? LessonResource::collection($this->lessons)
                : null,

            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
        ];
    }
}
