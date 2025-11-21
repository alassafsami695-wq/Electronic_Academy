<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "password" => "required"
        ]);

        if (!Auth::attempt($request->only("email", "password"))) {
            return response()->json(["message" => "Invalid credentials"], 401);
        }

        $user = Auth::user();

        if (!$user->is_email_verified) {

            // توليد كود تحقق من 6 أرقام
            $code = rand(100000, 999999);

            $user->email_verification_code = $code;
            $user->save();

            // إرسال الإيميل
            Mail::send("emails.verification_code", ["user" => $user], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject("Your Verification Code");
            });

            return response()->json([
                "message" => "Verification code sent to your email",
                "need_verification" => true
            ]);
        }

        return response()->json([
            "message" => "Login successful",
            "token" => $user->createToken("api_token")->plainTextToken
        ]);
    }


    public function verifyEmail(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "code" => "required"
        ]);

        $user = User::where("email", $request->email)->first();

        if (!$user || $user->email_verification_code != $request->code) {
            return response()->json(["message" => "Invalid verification code"], 400);
        }

        $user->is_email_verified = true;
        $user->email_verification_code = null;
        $user->save();

        return response()->json([
            "message" => "Email verified successfully",
            "token" => $user->createToken("api_token")->plainTextToken
        ]);
    }
}
