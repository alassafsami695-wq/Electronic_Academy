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

            'courses'     => $this->courses->map(function ($course) {
                return [
                    'id'          => $course->id,
                    'title'       => $course->title,
                    'description' => $course->description,
                    'price'       => $course->price,
                    'photo'       => $course->photo ? asset('storage/' . $course->photo) : null,
                    'progress'    => $course->progress_percentage ?? 0,
                    'teacher'     => $course->teacher ? [
                        'id'    => $course->teacher->id,
                        'name'  => $course->teacher->name,
                        'email' => $course->teacher->email,
                    ] : null,
                ];
            }),
        ];
    }
}
