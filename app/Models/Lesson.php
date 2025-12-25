<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Comment;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lesson extends Model
{

    use HasFactory;
    
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

    //---------------- الأسئلة المرتبطة بالدرس -----------------
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    //---------------- المستخدمون الذين أكملوا هذا الدرس -----------------
    public function completedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'lesson_completions')
                    ->withPivot('completed_at')
                    ->withTimestamps();
    }
}
