<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // أضف هذا
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory; // أضف هذا

    protected $fillable = [
        'lesson_id',
        'type',
        'question',
        'options',
        'answer',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    protected $hidden = [
    'answer', // سيتم إخفاء الإجابة تلقائياً في كل الـ APIs
    ];
}