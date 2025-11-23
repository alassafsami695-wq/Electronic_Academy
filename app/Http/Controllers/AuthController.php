<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;
use Laravel\Sanctum\HasApiTokens;

class AuthController extends Controller
{
        use HasApiTokens, Notifiable;
    /**
     * -------------------------
     * REGISTER
     * -------------------------
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'is_verified' => false,
        ]);

        // إنشاء كود تحقق
        $code = rand(100000, 999999);
        $user->email_verification_code = $code;
        $user->save();

        // إرسال الكود إلى البريد
        Mail::to($user->email)->send(new VerificationCodeMail($code));

        return response()->json([
            'message' => 'User registered successfully. Verification code sent to email.'
        ], 201);
    }

    /**
     * -------------------------
     * LOGIN
     * -------------------------
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!$user->is_verified) {
            // لو الحساب غير مفعّل أرسل له كود جديد
            $code = rand(100000, 999999);
            $user->email_verification_code = $code;
            $user->save();

            Mail::to($user->email)->send(new VerificationCodeMail($code));

            return response()->json([
                'message' => 'Your account is not verified. Verification code sent to email.',
                'requires_verification' => true
            ], 403);
        }

        // إنشاء التوكن
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    /**
     * -------------------------
     * VERIFY EMAIL
     * -------------------------
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|numeric',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->email_verification_code != $request->code) {
            return response()->json(['message' => 'Invalid verification code'], 400);
        }

        $user->is_verified = true;
        $user->email_verification_code = null;
        $user->save();

        // إنشاء توكن بعد التفعيل
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'message' => 'Email verified successfully',
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    /**
     * -------------------------
     * LOGOUT (حذف توكن واحد)
     * -------------------------
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * -------------------------
     * LOGOUT FROM ALL DEVICES
     * -------------------------
     */
    public function logoutAllDevices(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices successfully'
        ]);
    }
}
