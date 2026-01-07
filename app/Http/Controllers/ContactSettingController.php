<?php

namespace App\Http\Controllers;

use App\Models\ContactSetting;
use Illuminate\Http\Request;

class ContactSettingController extends Controller
{
    
    //  عرض معلومات التواصل للجميع (Public)
     
    public function index()
    {
        $settings = ContactSetting::first();
        return response()->json([
            'status' => true,
            'data' => $settings
        ]);
    }

    
    //  تحديث أو إنشاء معلومات التواصل (للأدمن فقط)
     
    public function update(Request $request)
    {
        $request->validate([
            'email' => 'nullable|email',
            'location' => 'nullable|string',
            'phone_primary' => 'nullable|string',
        ]);

        // updateOrCreate تضمن وجود سجل واحد فقط دائماً
        $settings = ContactSetting::updateOrCreate(
            ['id' => 1], // نبحث دائماً عن السجل رقم 1
            $request->all()
        );

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث معلومات التواصل بنجاح',
            'data' => $settings
        ]);
    }
}