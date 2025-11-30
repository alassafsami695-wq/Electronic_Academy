<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    
     // --------------------Register a new user + send numeric verification code------------------
     
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        
        $verificationCode = rand(100000, 999999);

        $studentRole = Role::where('name', 'user')->firstOrFail();

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'email_verification_code' => $verificationCode,
            'role_id' => $studentRole->id,
            'is_verified' => false,
        ]);

        // إرسال رسالة التحقق
        Mail::raw("Your verification code is: " . $verificationCode, function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Email Verification Code');
        });

        return response()->json([
            'message' => 'User registered successfully. Verification email sent.',
            'user' => $user
        ], 201);
    }


    
    // ------------------------Login user----------------------------------------
     
public function login(Request $request)
{
    $request->validate([
        'email'    => 'required|string|email',
        'password' => 'required|string',
    ]);

    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $user = Auth::user();

    if (!$user->is_verified) {
        return response()->json(['message' => 'Email not verified'], 403);
    }

    //-----------------إنشاء أو تحديث المحفظة (إضافة 1000$ عند أول تسجيل دخول فقط)----------------
    if (!$user->wallet) {
        $user->wallet()->create([
            'balance' => 1000
        ]);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message'      => 'Login successful',
        'access_token' => $token,
        'token_type'   => 'Bearer',
        'user'         => $user->load('wallet'),
    ], 200);
}



    
     // -------------------Logout user------------------------
     
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }


    
     // ------------------------Verify email--------------------------
     
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->email_verification_code != $request->code) {
            return response()->json(['message' => 'Invalid verification code'], 400);
        }

        $user->is_verified = true;
        $user->email_verified_at = now();
        $user->email_verification_code = null;
        $user->save();

        return response()->json([
            'message' => 'Email verified successfully',
            'user'    => $user
        ], 200);
    }
}

