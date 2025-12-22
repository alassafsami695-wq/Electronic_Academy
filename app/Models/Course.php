<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    protected $fillable = [
        'title',
        'description',
        'photo',
        'price',
        'course_duration',
        'number_of_students',
        'rating',
        'teacher_id',
        'path_id',
        'sales_count',
    ];

    protected $casts = [
        'price'              => 'float',
        'rating'             => 'float',
        'number_of_students' => 'integer',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function path(): BelongsTo
    {
        return $this->belongsTo(Path::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    // العلاقة الجديدة للتحقق من المشتركين
    public function enrolledUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_user');
    }

    // ---- Query scopes ----
    public function scopeVisibleFor($query, ?User $user = null)
    {
        if ($user && $user->isTeacher()) {
            return $query->where('teacher_id', $user->id);
        }
        return $query;
    }

    // ---- Accessors ----
    public function getProgressPercentageAttribute(): float
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return 0.0;
        }

        $lessonIds = $this->relationLoaded('lessons')
            ? $this->lessons->pluck('id')
            : $this->lessons()->pluck('id');

        $totalLessons = $lessonIds->count();
        if ($totalLessons === 0) {
            return 0.0;
        }

        $completedLessonsCount = $user->completedLessons()
            ->whereIn('lesson_id', $lessonIds)
            ->count();

        return round(($completedLessonsCount / $totalLessons) * 100, 2);
    }

    public function getTeacherNameAttribute(): ?string
    {
        return optional($this->teacher)->name;
    }

    public function getPathTitleAttribute(): ?string
    {
        return optional($this->path)->title;
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }
}