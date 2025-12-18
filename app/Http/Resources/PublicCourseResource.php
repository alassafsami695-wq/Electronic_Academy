<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicCourseResource extends JsonResource
{
public function toArray(Request $request): array
{
    $user = auth()->user();
    $isEnrolled = false;

    if ($user && method_exists($user, 'enrolledCourses')) {
        $isEnrolled = $user->enrolledCourses->contains($this->id);
    }

    $totalDuration = $this->lessons()->sum('duration');

    return [
        'id' => $this->id,
        'title' => $this->title,
        'description' => $this->description,
        'photo' => $this->photo ? asset('storage/' . $this->photo) : null,
        'price' => $this->price,
        'rating' => $this->rating,
        'number_of_students' => $this->number_of_students,
        'lessons_count' => $this->lessons()->count(),
        'total_duration_minutes' => $totalDuration,
        'progress' => null, // أو احسبها إذا كان مشترك
        'is_enrolled' => $isEnrolled,
        'teacher' => [
            'id' => $this->teacher->id,
            'name' => $this->teacher->name,
        ],
        'path' => $this->path ? $this->path->name : null,
    ];
}


}
