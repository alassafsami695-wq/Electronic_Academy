<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CourseResource;

class PathResource extends JsonResource
{
    public function toArray($request)
    {
        $user = auth()->user();
        $enrolledCourseIds = $user ? $user->enrolledCourses()->pluck('courses.id')->toArray() : [];

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'courses' => $this->courses->map(function($course) use ($enrolledCourseIds) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'price' => $course->price,
                    'photo' => $course->photo ? asset('storage/' . $course->photo) : null,
                    'is_enrolled' => in_array($course->id, $enrolledCourseIds),
                    'progress' => in_array($course->id, $enrolledCourseIds) ? $course->progress_percentage : 0,
                    'teacher' => $course->teacher ? [
                        'id' => $course->teacher->id,
                        'name' => $course->teacher->name,
                        'email' => $course->teacher->email,
                    ] : null,
                ];
            }),
        ];
    }
}
