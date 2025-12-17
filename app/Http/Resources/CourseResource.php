<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            //---------------- معلومات أساسية عن الكورس ----------------
            'id'                 => $this->id,
            'title'              => $this->title,
            'description'        => $this->description,

            //---------------- صورة الكورس ----------------
            'photo'              => $this->photo 
                                    ? asset('storage/' . $this->photo) 
                                    : null,

            //---------------- بيانات إضافية ----------------
            'price'              => $this->price,
            'course_duration'    => $this->course_duration,
            'number_of_students' => $this->number_of_students,
            'rating'             => $this->rating,

            //---------------- نسبة التقدم (إن وُجد مستخدم) ----------------
            // يتم حسابها من Accessor داخل موديل Course
            'progress'           => $this->when(
                                        auth()->check(),
                                        fn() => $this->progress_percentage
                                    ),

            //---------------- الدروس ----------------
            'lessons'            => LessonResource::collection(
                                        $this->whenLoaded('lessons')
                                    ),

            //---------------- المعلم ----------------
            'teacher'            => new UserResource(
                                        $this->whenLoaded('teacher')
                                    ),

            //---------------- المسار ----------------
            'path'               => new PathResource(
                                        $this->whenLoaded('path')
                                    ),

            //---------------- التواريخ ----------------
            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
        ];
    }
}
