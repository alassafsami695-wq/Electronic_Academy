<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory; 

class Course extends Model
{

    use HasFactory; 
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

    protected $appends = ['photo_url', 'teacher_name', 'path_title', 'progress_percentage'];

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
    if (!$this->photo) {
        return null;
    }

    // هنا نقوم بربط المسار بـ asset مباشرة دون إضافة storage
    // النتيجة ستكون: http://localhost:8000/uploads/course/ai.jpg
    return asset($this->photo); 
}


// من أضاف هذا الكورس للمفضلة
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlists', 'course_id', 'user_id')
                    ->withTimestamps();
    }

        public function questions()
    {
        // الكورس لديه أسئلة من خلال الدروس
        return $this->hasManyThrough(Question::class, Lesson::class);
    }
}