<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    /**
     * تحويل بيانات الدرس إلى مصفوفة JSON.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'        => $this->id,
            'course_id' => $this->course_id,
            'title'     => $this->title,
            'order'     => $this->order,

            // ✅ تأكد أن الفيديو يظهر كرابط كامل إذا موجود
            'video_url' => $this->video_url 
                ? asset('storage/' . $this->video_url) 
                : null,

            'content'   => $this->content,

            // ✅ أضف التعليقات فقط إذا تم تحميل العلاقة
            'comments'  => $this->relationLoaded('comments')
                ? CommentResource::collection($this->comments)
                : null,

            // ✅ صياغة التاريخ بشكل موحد
            'created_at'=> $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'=> $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
