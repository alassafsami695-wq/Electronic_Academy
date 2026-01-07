<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'photo',
        'address',
        'phone_number',
        'birth_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor لتنظيف رابط الصورة وضمان ظهوره بشكل صحيح
     */
    protected function photo(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!$value) return null;

                // إذا كان الرابط مكرراً أو يحتوي على http مرتين (بسبب أخطاء سابقة)
                // سنقوم باستخراج المسار النسبي فقط (uploads/...)
                if (str_contains($value, 'http')) {
                    $parts = explode('uploads/', $value);
                    $relativePath = 'uploads/' . end($parts);
                    return asset($relativePath);
                }

                // الحالة الطبيعية: إذا كان المسار مخزناً كـ uploads/users/file.jpg
                return asset($value);
            }
        );
    }
}