<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdvertisementController extends Controller
{
    
     // عرض الإعلانات النشطة فقط للواجهة الأمامية
     
    public function index()
    {
        $ads = Advertisement::active()->get();

        return response()->json([
            'success' => true,
            'data' => $ads
        ], 200);
    }

    
     // تخزين إعلان جديد (خاص بالأدمن)
     
    public function store(Request $request)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'image'      => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'link'       => 'nullable|url',
            'description' => 'nullable|string', // التحقق من الوصف            
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);

        $path = $request->file('image')->store('ads', 'public');

        $ad = Advertisement::create([
            'title'      => $request->title,
            'image_path' => $path,
            'link'       => $request->link,
            'description' => $request->description,           
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'is_active'  => $request->is_active ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الإعلان بنجاح',
            'data'    => $ad
        ], 201);
    }

    
     // حذف إعلان مع حذف صورته من السيرفر
     
    public function destroy($id)
    {
        $ad = Advertisement::findOrFail($id);

        if ($ad->image_path) {
            Storage::disk('public')->delete($ad->image_path);
        }

        $ad->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الإعلان وصورته بنجاح'
        ], 200);
    }

    
    //  تفعيل أو تعطيل الإعلان بسرعة
     
    public function toggleStatus($id)
    {
        $ad = Advertisement::findOrFail($id);
        $ad->update(['is_active' => !$ad->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الإعلان بنجاح',
            'is_active' => $ad->is_active
        ], 200);
    }
}