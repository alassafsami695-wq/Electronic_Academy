<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'path_id',
        'teacher_id',
        'title',
        'summary',
        'price',
        'is_published',
    ];

    /**
     * العلاقة مع المعلم
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * العلاقة مع المسار
     */
    public function path(): BelongsTo
    {
        return $this->belongsTo(Path::class);
    }

    /**
     * العلاقة مع الدروس
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    /**
     * نسبة تقدم المستخدم في الكورس
     */
    public function getProgressPercentageAttribute(): float
    {
        $user = Auth::user();

        if (!$user || $this->lessons->isEmpty()) {
            return 0.0;
        }

        $totalLessons = $this->lessons->count();
        $completedLessonsCount = $user->completedLessons()
            ->whereIn('lesson_id', $this->lessons->pluck('id'))
            ->count();

        if ($totalLessons === 0) {
            return 0.0;
        }

        return round(($completedLessonsCount / $totalLessons) * 100, 2);
    }
}
