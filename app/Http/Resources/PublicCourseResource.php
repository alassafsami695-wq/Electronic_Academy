<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicCourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // حساب وقت الكورس (مجموع مدة الدروس بالدقائق)
        $totalDuration = $this->lessons()->sum('duration');

        // حساب نسبة الإكمال إذا كان المستخدم مشترك
        $progress = null;

        if (auth()->check() && auth()->user()->enrolledCourses->contains($this->id)) {

            $user = auth()->user();

            $totalLessons = $this->lessons()->count();

            $completedLessons = $user->completedLessons()
                ->whereIn('lesson_id', $this->lessons->pluck('id'))
                ->count();

            $progress = $totalLessons > 0
                ? round(($completedLessons / $totalLessons) * 100, 2)
                : 0;
        }

        return [

            //---------------- معلومات أساسية ----------------
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,

            //---------------- صورة الكورس ----------------
            'photo'       => $this->photo
                                ? asset('storage/' . $this->photo)
                                : null,

            //---------------- بيانات إضافية ----------------
            'price'                   => $this->price,
            'rating'                  => $this->rating,
            'number_of_students'      => $this->number_of_students,
            'lessons_count'           => $this->lessons()->count(),
            'total_duration_minutes'  => $totalDuration,

            //---------------- نسبة الإكمال (إن كان الطالب مشترك) ----------------
            'progress' => $progress,

            //---------------- معلومات المدرس ----------------
            'teacher' => [
                'id'   => $this->teacher->id,
                'name' => $this->teacher->name,
            ],

            //---------------- المسار ----------------
            'path' => $this->path ? $this->path->name : null,
        ];
    }
}
