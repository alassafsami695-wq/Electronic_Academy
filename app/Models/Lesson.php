<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id', 
        'title', 
        'order', 
        'video_url', 
        'content',
        'duration'
    ];

    // ✅ إضافة الحقل الوهمي لكي يظهر في استجابة JSON في Postman
    protected $appends = ['video_full_url'];

    // ✅ Accessor يحول المسار المخزن في قاعدة البيانات إلى رابط كامل شغال
    public function getVideoFullUrlAttribute(): ?string
    {
        if (!$this->video_url) {
            return null;
        }

        // بما أن ملفاتك موجودة في public/uploads مباشرة
        // دالة asset ستقوم بإنشاء الرابط الصحيح: http://localhost:8000/uploads/lesson/...
        return asset($this->video_url); 
    }

    //---------------- العلاقات -----------------

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)
                    ->whereNull('parent_id')
                    ->orderBy('created_at', 'desc');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function completedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'lesson_completions')
                    ->withPivot('completed_at')
                    ->withTimestamps();
    }
}