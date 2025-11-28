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

        $studentRole = Role::where('name', 'student')->firstOrFail();

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

    // ----------------- كورسات المستخدم الحالي (طالب/أستاذ) -----------------
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
