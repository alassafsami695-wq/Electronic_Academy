<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Exception;

class AuthController extends Controller
{
    // -------------------- تسجيل طالب جديد ------------------
    public function registerStudent(Request $request)
    {
        return $this->handleRegistration($request, 'user');
    }

    // -------------------- تسجيل أستاذ جديد ------------------
    public function registerTeacher(Request $request)
    {
        return $this->handleRegistration($request, 'teacher');
    }

    // -------------------- منطق التسجيل المشترك ------------------
    private function handleRegistration(Request $request, $roleName)
    {
        // 1. التحقق من البيانات
        $validatedData = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|string|email|unique:users',
            'password'        => 'required|string|min:6',
            'wallet_password' => 'required|string|min:4', 
            'account_number'  => 'nullable|string',      
        ]);

        $verificationCode = rand(100000, 999999);
        $role = Role::where('name', $roleName)->firstOrFail();

        try {
            // 2. استخدام Transaction لضمان إنشاء (المستخدم + المحفظة)
            $user = DB::transaction(function () use ($validatedData, $verificationCode, $role, $request) {
                
                $user = User::create([
                    'name'                    => $validatedData['name'],
                    'email'                   => $validatedData['email'],
                    'password'                => Hash::make($validatedData['password']),
                    'email_verification_code' => $verificationCode,
                    'role_id'                 => $role->id,
                    'is_verified'             => false,
                    'status'                  => 'active', // الحساب نشط افتراضياً حتى يتم تعليقه من الأدمن
                ]);

                // إنشاء المحفظة
                $user->wallet()->create([
                    'balance'         => 0.00,
                    'account_number'  => $request->account_number ?? 'SHAM-' . rand(10000, 99999),
                    'wallet_password' => Hash::make($validatedData['wallet_password']),
                ]);

                return $user;
            });

            // 3. إرسال بريد التحقق
            Mail::raw("كود التحقق الخاص بك في الأكاديمية الإلكترونية هو: " . $verificationCode, function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Email Verification Code');
            });

            return response()->json([
                'status'  => 'success',
                'message' => "تم التسجيل بنجاح كـ " . ($roleName == 'teacher' ? 'أستاذ' : 'طالب') . "، يرجى التحقق من بريدك الإلكتروني.",
                'user'    => $user->load('wallet')
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'فشل عملية التسجيل: ' . $e->getMessage()
            ], 500);
        }
    }

    // ------------------------ تسجيل الدخول (مع فحص حالة الحساب) ------------------------
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'بيانات الاعتماد غير صحيحة'], 401);
        }

        $user = Auth::user();

        // فحص إذا كان الحساب معلقاً من قبل الأدمن
        if ($user->status === 'suspended') {
            Auth::logout();
            return response()->json(['message' => 'عذراً، حسابك معلق حالياً، يرجى التواصل مع الإدارة'], 403);
        }

        if (!$user->is_verified) {
            return response()->json(['message' => 'يرجى تفعيل الحساب أولاً عبر البريد الإلكتروني'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => 'تم تسجيل الدخول بنجاح',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'status' => $user->status,
                'type'   => $user->is_super_admin ? 'super_admin' : ($user->role->name ?? 'user'),
                'wallet' => $user->wallet,
            ]
        ], 200);
    }

    // ------------------------ التحقق من الإيميل وتفعيل الحساب ------------------------
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'المستخدم غير موجود'], 404);
        }

        if ($user->email_verification_code != $request->code) {
            return response()->json(['message' => 'كود التحقق غير صحيح'], 400);
        }

        $user->is_verified = true;
        $user->email_verified_at = now();
        $user->email_verification_code = null;
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => 'تم تفعيل الحساب بنجاح، تم تسجيل دخولك تلقائياً.',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'type'   => $user->is_super_admin ? 'super_admin' : ($user->role->name ?? 'user'),
                'wallet' => $user->wallet,
            ]
        ], 200);
    }

    // ------------------------ تسجيل الخروج ------------------------
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'تم تسجيل الخروج بنجاح']);
    }
}