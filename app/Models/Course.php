<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    //---------------- الحقول القابلة للتعبئة -----------------
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

    //---------------- التحويلات (Casting) -----------------
    protected $casts = [
        'price'              => 'float',   
        'rating'             => 'float',   
        'number_of_students' => 'integer', 
    ];

    //---------------- العلاقة مع المعلم -----------------
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    //---------------- العلاقة مع المسار -----------------
    public function path(): BelongsTo
    {
        return $this->belongsTo(Path::class);
    }

    //---------------- العلاقة مع الدروس -----------------
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    //---------------- نسبة تقدم المستخدم في الكورس -----------------
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

        // حساب نسبة التقدم بدقة (من 0 إلى 100)
        return round(($completedLessonsCount / $totalLessons) * 100, 2);
    }

    //---------------- اسم المعلم المرتبط بالكورس -----------------
    public function getTeacherNameAttribute(): ?string
    {
        return $this->teacher->name ?? null;
    }

    //---------------- عنوان المسار المرتبط بالكورس -----------------
    public function getPathTitleAttribute(): ?string
    {
        return $this->path->title ?? null;
    }

    //---------------- رابط الصورة المخزنة للكورس -----------------
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }
}
