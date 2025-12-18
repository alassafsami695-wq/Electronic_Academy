<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\LessonResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\PathResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = auth()->user();

        // جلب كل IDs مرة واحدة لتحسين الأداء
        $enrolledCourseIds = $user ? $user->enrolledCourses()->pluck('courses.id')->toArray() : [];
        $isEnrolled = in_array($this->id, $enrolledCourseIds);

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
            'lessons'            => $this->when(
                $isEnrolled,
                fn () => LessonResource::collection($this->whenLoaded('lessons'))
            ),
            'progress'           => $this->when(
                $isEnrolled,
                fn () => $this->progress_percentage
            ),
            'teacher'            => new UserResource($this->whenLoaded('teacher')),
            'path' => [
                'id' => $this->path->id,
                'title' => $this->path->title,
            ],
            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
        ];
    }
}
