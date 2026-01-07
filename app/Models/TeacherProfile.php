<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class TeacherProfile extends Model
{
    protected $fillable = [
        'user_id',
        'photo',
        'facebook_url',
        'linkedin_url',
        'instagram_url',
        'youtube_url',
        'github_url',
        'address',
        'phone_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor لضمان ظهور رابط الصورة بشكل صحيح ونظيف
     */
    protected function photo(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!$value) return null;

                // إذا كانت القيمة المخزنة تبدأ بـ http، فهذا يعني أنها رابط كامل أصلاً
                // سنقوم بتنظيفها للتأكد من عدم وجود تكرار (بسبب أخطاء السدير السابقة)
                if (str_starts_with($value, 'http')) {
                    // نأخذ اسم الملف فقط ونعيد بناء الرابط بشكل صحيح
                    $pathParts = explode('uploads/', $value);
                    $relativePath = 'uploads/' . end($pathParts);
                    return asset($relativePath);
                }

                // إذا كان مساراً عادياً مثل uploads/users/teacher.jpg
                return asset($value);
            }
        );
    }
}