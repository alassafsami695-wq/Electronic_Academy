<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('is.Admin');
    }

    public function storeAdmin(Request $request)
    {
       // $this->authorize('createAdmin', User::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $user = User::create([
            'name'            => $request->name,
            'email'           => $request->email,
            'password'        => Hash::make($request->password),
            'role_id'         => $adminRole->id,
            'is_super_admin'  => false,
            'is_verified'     => true,
            'email_verified_at' => now(),
        ]);

        return response()->json([
            'message' => 'Admin account created successfully (Secondary Admin)',
            'user'    => $user
        ], 201);
    }

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
