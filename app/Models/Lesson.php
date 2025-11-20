<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Comment;

class Lesson extends Model
{
    protected $fillable = [
        'course_id',
        'title',
        'order',
        'video_url',
        'content',
    ];

    /**
     * الكورس الذي ينتمي إليه الدرس
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * التعليقات الأساسية (الأب) للدرس
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id')->orderBy('created_at', 'desc');
    }

    /**
     * جميع التعليقات بدون فلترة (اختياري)
     */
    public function allComments(): HasMany
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');
    }
}
