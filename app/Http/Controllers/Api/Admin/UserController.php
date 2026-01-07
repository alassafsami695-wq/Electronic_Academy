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

    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $query = User::query();

        if (!$currentUser->is_super_admin) {
            $query->whereHas('role', function($q) {
                $q->whereIn('name', ['teacher', 'user']);
            });
        }

        if ($request->has('role')) {
            $roleName = $request->input('role');
            $query->whereHas('role', function ($q) use ($roleName) {
                $q->where('name', $roleName);
            });
        }

        $users = $query->with(['role', 'wallet'])->paginate(15);
        return response()->json(['message' => 'Users retrieved successfully', 'data' => $users]);
    }

    public function show(User $user)
    {
        $user->load('role', 'wallet');
        return response()->json(['data' => $user]);
    }

    public function storeAdmin(Request $request)
    {
        if (!auth()->user()->is_super_admin) {
            return response()->json(['message' => 'فقط السوبر أدمن يمكنه إضافة أدمن جديد'], 403);
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
            'status'            => 'active',
            'email_verified_at' => now(),
        ]);

        return response()->json(['message' => 'تم إنشاء حساب الأدمن بنجاح', 'user' => $user], 201);
    }

    // ----------------- تعليق / إنهاء تعليق الحساب -----------------
    public function toggleStatus(User $user)
    {
        $currentUser = auth()->user();

        // حماية حسابات الإدارة من التعديل بواسطة أدمن عادي
        if (!$currentUser->is_super_admin && ($user->role->name === 'admin' || $user->is_super_admin)) {
            return response()->json(['message' => 'غير مصرح لك بتغيير حالة هذا الحساب'], 403);
        }

        // تبديل الحالة
        if ($user->status === 'active') {
            $user->status = 'suspended';
            $msg = 'تم تعليق حساب المستخدم بنجاح';
        } else {
            $user->status = 'active';
            $msg = 'تم إنهاء تعليق الحساب وتفعيله بنجاح';
        }
        
        $user->save();

        return response()->json([
            'message' => $msg,
            'new_status' => $user->status
        ]);
    }

    public function storePath(StorePathRequest $request)
    {
        $data = $request->validated();
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
            if ($path->photo) { Storage::disk('public')->delete($path->photo); }
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

    public function teacherCourses(User $teacher)
    {
        $courses = Course::where('teacher_id', $teacher->id)->with('path')->get();
        return response()->json(['data' => $courses]);
    }
    public function getStudentsWithCourseCount()
{
    // جلب المستخدمين الذين يملكون دور 'user' مع عد علاقة الكورسات لديهم
    $students = User::whereHas('role', function($q) { $q->where('name', 'user'); })
        ->withCount('courses') 
        ->get();

    return response()->json(['status' => true, 'data' => $students]);
}

    
}