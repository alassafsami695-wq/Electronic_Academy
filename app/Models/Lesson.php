<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Comment;

class Lesson extends Model
{
    //---------------- الحقول القابلة للتعبئة -----------------
    protected $fillable = [
        'course_id',   
        'title',       
        'order',       
        'video_url',   
        'content',     
    ];

    //---------------- العلاقة مع الكورس -----------------
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    //---------------- التعليقات الجذرية (الأب فقط) -----------------
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)
                    ->whereNull('parent_id')
                    ->orderBy('created_at', 'desc');
    }

    //---------------- جميع التعليقات (بما فيها الردود) -----------------
    public function allComments(): HasMany
    {
        return $this->hasMany(Comment::class)
                    ->orderBy('created_at', 'desc');
    }
        public function questions()
    {
        return $this->hasMany(Question::class);
    }

}
