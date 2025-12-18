<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePathRequest;
use App\Http\Requests\UpdatePathRequest;
use App\Http\Resources\PathResource;
use App\Models\User;
use App\Models\Role;
use App\Models\Course;
use App\Models\Path;
use App\Models\Comment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('is.Admin');
    }

    // ----------------- قائمة المستخدمين -----------------
    public function index(Request $request)
    {
        $currentUser = auth()->user();

        $query = User::query();

        // ------------------- Super Admin: يشوف الكل -------------------
        if ($currentUser->is_super_admin) {

            $query->whereHas('role', function($q) {
                $q->whereIn('name', ['admin', 'teacher', 'user']);
            });

        }
        // ------------------- Admin: يشوف فقط teachers + users -------------------
        else {

            $query->whereHas('role', function($q) {
                $q->whereIn('name', ['teacher', 'user']);
            });
        }

        // ----------- فلترة بحسب الدور إذا كانت موجودة بالـ request -----------
        if ($request->has('role')) {

            $roleName = $request->input('role');

            // منع ال admin من رؤية admin حتى لو طلب role=admin
            if (!$currentUser->is_super_admin && $roleName === 'admin') {
                return response()->json([
                    'message' => 'Admins can only view teachers and users'
                ], 403);
            }

            $query->whereHas('role', function ($q) use ($roleName) {
                $q->where('name', $roleName);
            });
        }

        $users = $query->with(['role', 'wallet'])->paginate(15);

        return response()->json([
            'message' => 'Users retrieved successfully',
            'data'    => $users
        ]);
    }

    // ----------------- عرض مستخدم محدد -----------------
    public function show(User $user)
    {
        $user->load('role', 'wallet');

        return response()->json([
            'message' => 'User retrieved successfully',
            'data'    => $user
        ]);
    }

    // ----------------- إضافة أدمن (فقط Super Admin) -----------------
    public function storeAdmin(Request $request)
    {
        $currentUser = auth()->user();

        if (!$currentUser->is_super_admin) {
            return response()->json([
                'message' => 'Only Super Admin can create Admin accounts'
            ], 403);
        }

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'role_id'           => $adminRole->id,
            'is_super_admin'    => false,
            'is_verified'       => true,
            'email_verified_at' => now(),
        ]);

        return response()->json([
            'message' => 'Admin account created successfully',
            'user'    => $user
        ], 201);
    }

    // ----------------- تعديل أدمن -----------------
    public function updateAdmin(Request $request, User $admin)
    {
        $request->validate([
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $admin->id,
        ]);

        $admin->update($request->only(['name','email']));

        return response()->json([
            'message' => 'Admin updated successfully',
            'user'    => $admin
        ]);
    }

    // ----------------- حذف أدمن (فقط Super Admin) -----------------
    public function destroyAdmin(User $admin)
    {
        $currentUser = auth()->user();

        if (!$currentUser->is_super_admin) {
            return response()->json([
                'message' => 'Only Super Admin can delete Admin accounts'
            ], 403);
        }

        if (!$admin->role || $admin->role->name !== 'admin') {
            return response()->json(['message' => 'Target user is not an admin'], 422);
        }

        $admin->delete();

        return response()->json(['message' => 'Admin deleted successfully']);
    }

    // ----------------- إدارة الأساتذة -----------------
    public function storeTeacher(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $teacherRole = Role::where('name', 'teacher')->firstOrFail();

        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'role_id'           => $teacherRole->id,
            'is_verified'       => true,
            'email_verified_at' => now(),
        ]);

        return response()->json([
            'message' => 'Teacher account created successfully',
            'user'    => $user
        ], 201);
    }

    public function updateTeacher(Request $request, User $teacher)
    {
        $request->validate([
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $teacher->id,
        ]);

        $teacher->update($request->only(['name','email']));

        return response()->json([
            'message' => 'Teacher updated successfully',
            'user'    => $teacher
        ]);
    }

    public function destroyTeacher(User $teacher)
    {
        if (!$teacher->role || $teacher->role->name !== 'teacher') {
            return response()->json(['message' => 'Target user is not a teacher'], 422);
        }

        $teacher->comments()->delete();
        $teacher->courses()->delete();
        $teacher->wallet()->delete();
        $teacher->enrolledCourses()->detach();
        $teacher->completedLessons()->detach();

        $teacher->delete();

        return response()->json(['message' => 'Teacher deleted successfully']);
    }

    // ----------------- إدارة الطلاب -----------------
    public function storeStudent(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $studentRole = Role::where('name', 'user')->firstOrFail();

        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'role_id'           => $studentRole->id,
            'is_verified'       => true,
            'email_verified_at' => now(),
        ]);

        return response()->json([
            'message' => 'Student account created successfully',
            'user'    => $user
        ], 201);
    }

    public function updateStudent(Request $request, User $student)
    {
        $request->validate([
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $student->id,
        ]);

        $student->update($request->only(['name','email']));

        return response()->json([
            'message' => 'Student updated successfully',
            'user'    => $student
        ]);
    }

    public function destroyStudent(User $student)
    {
        if (!$student->role || $student->role->name !== 'user') {
            return response()->json(['message' => 'Target user is not a student'], 422);
        }

        $student->comments()->delete();
        $student->wallet()->delete();
        $student->enrolledCourses()->detach();
        $student->completedLessons()->detach();

        $student->delete();

        return response()->json(['message' => 'Student deleted successfully']);
    }

    // ----------------- كورسات الأستاذ -----------------
    public function teacherCourses(User $teacher)
    {
        $courses = Course::where('teacher_id', $teacher->id)->with('path')->get();
        return response()->json(['data' => $courses]);
    }

   public function destroyCourse(Course $course)
    {
        $user = auth()->user();

        // فقط الأدمن يمكنه حذف كورسات المدرّسين
        if (!$user->hasRole('Admin')) {
            return response()->json(['message' => 'غير مصرح لك بحذف هذا الكورس'], 403);
        }

        if ($course->photo) {
            Storage::disk('public')->delete($course->photo);
        }

        $course->delete();

        return response()->json(['message' => 'تم حذف الكورس بنجاح بواسطة الأدمن']);
    }


    // ----------------- إدارة المسارات -----------------
    public function storePath(StorePathRequest $request)
    {
       $data = $request->validated();

            // 📸 رفع الصورة إن وجدت
            if ($request->hasFile('photo')) {
                $data['photo'] = $request->file('photo')->store('paths', 'public');
            }

            $path = Path::create($data);
            return new PathResource($path);


    }

    public function updatePath(UpdatePathRequest $request, Path $path)
    {
         $data = $request->validated();

        if ($request->hasFile('photo')) {
            if ($path->photo) {
                Storage::disk('public')->delete($path->photo);
            }
            $data['photo'] = $request->file('photo')->store('paths', 'public');
        }

        $path->update($data);

        return new PathResource($path);
    }

    public function destroyPath(Path $path)
    {
        $path->delete();
        return response()->json(['message' => 'Path deleted successfully']);
    }

    // ----------------- إدارة التعليقات -----------------

    // public function destroyComment(Comment $comment)
    // {
    //     // فقط الأدمن يستطيع الوصول لهذا الراوت، فلا حاجة لفحص الدور هنا

    //     // حذف الردود أولاً إن وجدت
    //     $comment->replies()->delete();

    //     // حذف التعليق نفسه
    //     $comment->delete();

    //     return response()->json([
    //         'message' => 'تم حذف التعليق بنجاح'
    //     ]);
    // }


    // ----------------- كورسات الطالب + التقدم -----------------
    public function getMyCourses()
    {
        $user = auth()->user();

        $courses = $user->enrolledCourses()->with('lessons')->get();

        $coursesWithProgress = $courses->map(function ($course) use ($user) {
            $totalLessons = $course->lessons->count();
            $completedLessons = $user->completedLessons()
                ->where('course_id', $course->id)
                ->count();

            $progress = $totalLessons > 0
                ? round(($completedLessons / $totalLessons) * 100, 2)
                : 0;

            return [
                'course'            => $course,
                'total_lessons'     => $totalLessons,
                'completed_lessons' => $completedLessons,
                'progress'          => $progress,
            ];
        });

        return response()->json([
            'message' => 'Courses retrieved successfully',
            'data'    => $coursesWithProgress
        ]);
    }
}
