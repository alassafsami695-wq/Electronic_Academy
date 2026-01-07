<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // الحقول الأساسية التي تظهر في الواجهة الرئيسية (القائمة)
        $data = [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'salary'      => $this->salary,
        ];

        // إضافة التفاصيل الإضافية فقط عند عرض وظيفة واحدة (show)
        // أو إذا كان الطلب يحتوي على باراميتر 'full_details'
        if ($request->routeIs('*.show') || $request->has('full_details')) {
            $data['company_name']  = $this->company_name;
            $data['company_email'] = $this->company_email;
            $data['job_type']      = $this->job_type;
            $data['working_hours'] = $this->working_hours;
            $data['created_at']    = $this->created_at->format('Y-m-d H:i:s');
        }

        return $data;
    }
}