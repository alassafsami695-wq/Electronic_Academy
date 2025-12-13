<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'lesson_id'   => $this->lesson_id,
            'parent_id'   => $this->parent_id,
            'body'        => $this->body,

            // صاحب التعليق
            'user'        => new UserResource($this->whenLoaded('user')),

            // الردود (nested)
            'replies'     => CommentResource::collection($this->whenLoaded('replies')),

            // عدد الردود
            'replies_count' => $this->whenLoaded('replies', fn () => $this->replies->count()),

            'created_at'  => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'  => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
