<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * الحقول القابلة للكتابة جماعياً
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_super_admin',
    ];

    /**
     * الحقول المخفية عند تحويل الموديل إلى JSON
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * الحقول التي يجب تحويلها إلى أنواع محددة
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_super_admin' => 'boolean',
    ];

    /**
     * علاقة المستخدم مع الدور
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * علاقة المستخدم مع محفظته
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * علاقة المستخدم مع الكورسات التي يدرسها (إذا هو teacher)
     */
    public function courses()
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    /**
     * علاقة المستخدم مع الكورسات التي يشترك بها
     */
    public function enrolledCourses()
    {
        return $this->belongsToMany(Course::class, 'course_user');
    }

    /**
     * علاقة المستخدم مع التعليقات التي كتبها
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * تحقق إذا كان المستخدم دور Admin
     */
    public function isAdmin(): bool
    {
        return $this->role?->name === 'admin' || $this->is_super_admin;
    }

    /**
     * تحقق إذا كان المستخدم دور Teacher
     */
    public function isTeacher(): bool
    {
        return $this->role?->name === 'teacher';
    }
}
