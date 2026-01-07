<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'status', 
        'email_verification_code',
        'is_verified',
        'is_super_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_super_admin'    => 'boolean',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function teacherProfile(): HasOne
    {
        return $this->hasOne(TeacherProfile::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_user', 'user_id', 'course_id')
                    ->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function completedLessons(): BelongsToMany
    {
        return $this->belongsToMany(Lesson::class, 'lesson_completions')
                    ->withPivot('completed_at')
                    ->withTimestamps();
    }

    // ---- Helpers ----
    public function isAdmin(): bool
    {
        return (bool) $this->is_super_admin
            || (optional($this->role)->name && strtolower($this->role->name) === 'admin');
    }

    public function isTeacher(): bool
    {
        return optional($this->role)->name && strtolower($this->role->name) === 'teacher';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }


// إضافة العلاقة مع الكورسات المفضلة
    public function wishlist(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'wishlists', 'user_id', 'course_id')
                    ->withTimestamps();
    }
}