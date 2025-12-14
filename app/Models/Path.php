<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Path extends Model
{
    //---------------- الحقول القابلة للتعبئة -----------------
    protected $fillable = [
        'title',
        'description',
        'photo'
    ];

    //---------------- التحويلات (Casting) -----------------
    protected $casts = [
        'tips' => 'array', // تحويل حقل النصائح من JSON إلى مصفوفة تلقائياً
    ];

    //---------------- العلاقة مع الكورسات -----------------
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

        public function course()
    {
        return $this->hasMany(Course::class);
    }

}
