<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Course;
use App\Models\Path;
use App\Models\Comment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

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
        $query = User::query();

        if ($request->has('role')) {
            $roleName = $request->input('role');
            $query->whereHas('role', function ($q) use ($roleName) {
                $q->where('name', $roleName);
            });
        }

        $users = $query->with('role', 'wallet')->paginate(15);

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

    // ----------------- إضافة أدمن -----------------
    public function storeAdmin(Request $request)
    {
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

    public function destroyAdmin(User $admin)
    {
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
        $course->delete();
        return response()->json(['message' => 'Course deleted successfully']);
    }

    // ----------------- إدارة المسارات -----------------
    public function storePath(Request $request)
    {
        $request->validate(['title' => 'required|string|max:255']);
        $path = Path::create($request->only('title'));
        return response()->json(['message' => 'Path created successfully', 'data' => $path], 201);
    }

    public function updatePath(Request $request, Path $path)
    {
        $request->validate(['title' => 'required|string|max:255']);
        $path->update($request->only('title'));
        return response()->json(['message' => 'Path updated successfully', 'data' => $path]);
    }

    public function destroyPath(Path $path)
    {
        $path->delete();
        return response()->json(['message' => 'Path deleted successfully']);
    }

    // ----------------- إدارة التعليقات -----------------
    public function destroyComment(Comment $comment)
    {
        $comment->delete();
        return response()->json(['message' => 'Comment deleted successfully']);
    }

    public function getMyCourses()
    {
        $user = auth()->user();

        $courses = $user->enrolledCourses()->with('lessons')->get();

        $coursesWithProgress = $courses->map(function ($course) {
            return [
                'id'                 => $course->id,
                'title'              => $course->title,
                'price'              => $course->price,
                'progress_percentage'=> $course->progress_percentage ?? 0,
            ];
        });

        return response()->json($coursesWithProgress);
    }
}
