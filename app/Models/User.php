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

    //----------------------الحقول القابلة للملء---------------------
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'email_verification_code',
        'is_verified',
        'is_super_admin',
    ];

    //--------------------------الحقول المخفية--------------------
    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_code',
    ];

    //-----------------------------التحويلات (casts)--------------------
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_super_admin' => 'boolean',
    ];

    
     //---------------------العلاقة مع الدور-------------------
     
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    
    //---------------------------علاقة المحفظة----------------------
     
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    
    //-------------------------------الكورسات التي يدرسها المستخدم------------------------
     
    public function courses()
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    
     //-----------------------------الكورسات التي يشترك بها المستخدم----------------------
     
    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_user')
                    ->withTimestamps();
    }


    //-----------------------------التعليقات التي كتبها المستخدم--------------------------
     
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    
   // ------------- الدروس المكتملة---------------------
     
    public function completedLessons(): BelongsToMany
    {
        return $this->belongsToMany(Lesson::class, 'lesson_completion')
                    ->withPivot('completed_at')
                    ->withTimestamps();
    }

    
     //--------------------------تحقق إذا كان المستخدم Admin------------------------
     
    public function isAdmin(): bool
    {
        // نتحقق أولاً من is_super_admin ثم من role
        return $this->is_super_admin || ($this->role && $this->role->name === 'admin');
    }

    
     //------------------------تحقق إذا كان المستخدم Teacher--------------------
     
    public function isTeacher(): bool
    {
        return $this->role && $this->role->name === 'teacher';
    }
}
