<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Course;
use App\Models\Comment;
use App\Policies\UserPolicy;
use App\Policies\CoursePolicy;
use App\Policies\CommentPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * السياسات المرتبطة بالموديلات
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class   => UserPolicy::class,
        Course::class => CoursePolicy::class,
        Comment::class=> CommentPolicy::class,
    ];

    /**
     * تسجيل السياسات
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // ✅ Gate لإنشاء أدمن جديد
        Gate::define('createAdmin', function ($user) {
            return $user->is_super_admin || $user->role_id === 1;
        });
    }
}
