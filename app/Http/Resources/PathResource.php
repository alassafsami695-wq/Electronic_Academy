<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CourseSummaryResource;


class PathResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'photo'       => $this->photo ? asset('storage/' . $this->photo) : null,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'courses'     => CourseResource::collection($this->whenLoaded('courses')),

        ];
        
    }
}
