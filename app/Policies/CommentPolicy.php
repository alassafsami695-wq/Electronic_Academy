<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Comment;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CommentPolicy
{
    use HandlesAuthorization;

    /**
     * الفحص قبل أي صلاحية
     */
    public function before(User $user, string $ability)
    {
        // السماح للمسؤول بالكامل
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    /**
     * السماح للمعلم فقط بالرد على التعليقات
     */
    public function replyToComment(User $user, Comment $comment)
    {
        return $user->isTeacher()
            ? Response::allow()
            : Response::deny('Only the teacher or admin can reply to this comment.');
    }

    /**
     * السماح بإنشاء التعليق
     */
    public function create(User $user)
    {
        return ($user->role && $user->role->name === 'user') || $user->isTeacher()
            ? Response::allow()
            : Response::deny('You are not allowed to create comments.');
    }
}
