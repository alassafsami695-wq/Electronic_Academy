<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    // الحقول القابلة للتعبئة
    protected $fillable = ['user_id', 'lesson_id', 'parent_id', 'body'];

    //---------------- صاحب التعليق -----------------
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    //---------------- الدرس الذي ينتمي إليه التعليق -----------------
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    //---------------- التعليق الأب إذا كان رد -----------------
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    //---------------- الردود على هذا التعليق -----------------
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }
}
