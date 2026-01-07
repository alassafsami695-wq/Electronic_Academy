<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    /**
     * تحويل بيانات الدرس إلى مصفوفة JSON.
     */
    public function toArray($request): array
    {
        return [
            'id'        => $this->id,
            'course_id' => $this->course_id,
            'title'     => $this->title,
            'order'     => $this->order,

            // ✅ التعديل الأساسي: نستخدم video_full_url من الموديل
            // هذا سيضمن أن الرابط يخرج بدون كلمة storage وبشكل صحيح
            'video_url' => $this->video_full_url, 

            'content'   => $this->content,

            // عرض التعليقات فقط إذا تم تحميل العلاقة (Eager Loading)
            'comments'  => $this->relationLoaded('comments')
                ? CommentResource::collection($this->comments)
                : null,

            'created_at'=> $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'=> $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}