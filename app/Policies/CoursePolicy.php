<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    use HandlesAuthorization;

    /**
     * السماح للـ Admin قبل أي صلاحية أخرى
     */
    public function before(User $user, string $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    /**
     * من يمكنه مشاهدة جميع الكورسات
     */
    public function viewAny(User $user): bool
    {
        return false; // فقط Admin أو سياسات مخصصة أخرى
    }

    /**
     * من يمكنه مشاهدة كورس محدد
     */
    public function view(User $user, Course $course): bool
    {
        return false; // حسب الحاجة يمكن السماح للطالب المشترك أو المدرس
    }

    /**
     * من يمكنه إنشاء كورس
     */
    public function create(User $user): Response
    {
        return $user->isTeacher()
            ? Response::allow()
            : Response::deny('Only teachers or admins can create courses.');
    }

    /**
     * من يمكنه تعديل كورس
     */
    public function update(User $user, Course $course): Response
    {
        return ($user->isTeacher() && $user->id === $course->teacher_id)
            ? Response::allow()
            : Response::deny('You can only update your own courses.');
    }

    /**
     * من يمكنه حذف كورس
     */
    public function delete(User $user, Course $course): Response
    {
        return ($user->isTeacher() && $user->id === $course->teacher_id)
            ? Response::allow()
            : Response::deny('You can only delete your own courses.');
    }

    /**
     * من يمكنه استعادة كورس (اختياري)
     */
    public function restore(User $user, Course $course): bool
    {
        return false;
    }

    /**
     * من يمكنه حذف كورس نهائيًا (force delete)
     */
    public function forceDelete(User $user, Course $course): bool
    {
        return false;
    }
}
