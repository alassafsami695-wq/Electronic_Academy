<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Lesson;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'email_verification_code',
        'is_email_verified',
        'is_super_admin'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_code'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_super_admin' => 'boolean',
    ];

    // العلاقة مع الدور
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    // علاقة المحفظة
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    // الكورسات التي يدرسها
    public function courses()
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    // الكورسات التي يشترك بها
    public function enrolledCourses()
    {
        return $this->belongsToMany(Course::class, 'course_user')->withTimestamps();
    }

    // التعليقات التي كتبها
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // الدروس المكتملة
    public function completedLessons(): BelongsToMany
    {
        return $this->belongsToMany(Lesson::class, 'lesson_completion')
                    ->withPivot('completed_at')
                    ->withTimestamps();
    }

    // تحقق إذا كان Admin
    public function isAdmin(): bool
    {
        return $this->role?->name === 'admin' || $this->is_super_admin;
    }

    // تحقق إذا كان Teacher
    public function isTeacher(): bool
    {
        return $this->role?->name === 'teacher';
    }
}
