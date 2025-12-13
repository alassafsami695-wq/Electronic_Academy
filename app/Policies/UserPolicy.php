<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * السماح بإنشاء مسؤول ثانوي (Admin) فقط إذا كان المستخدم Super Admin
     */
    public function createAdmin(User $user): Response
    {
        return $user->isSuperAdmin()
            ? Response::allow()
            : Response::deny('Only super admins can create other admins.');
    }
}
