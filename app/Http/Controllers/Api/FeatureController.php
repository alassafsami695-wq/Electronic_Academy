<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class FeatureController extends Controller
{
    /**
     * عرض جميع المميزات (للزوار والطلاب)
     */
    public function index()
    {
        $features = Feature::all();
        
        $features->transform(function ($feature) {
            if ($feature->image) {
                $feature->image = asset('storage/' . $feature->image);
            }
            return $feature;
        });

        return response()->json([
            'status' => 'success',
            'data' => $features
        ]);
    }


    /**
 * عرض ميزة واحدة محددة
 */
    public function show($id)
    {
        // البحث عن الميزة بواسطة المعرف
        $feature = Feature::find($id);

        // إذا لم يتم العثور على الميزة، أرسل خطأ 404
        if (!$feature) {
            return response()->json([
                'status' => 'error',
                'message' => 'الميزة غير موجودة'
            ], 404);
        }

        // تجهيز رابط الصورة ليظهر بشكل كامل
        if ($feature->image) {
            $feature->image = asset('storage/' . $feature->image);
        }

        return response()->json([
            'status' => 'success',
            'data' => $feature
        ]);
    }
    /**
     * إضافة ميزة جديدة (خاص بالأدمن)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // اختيارية
        ]);

        return DB::transaction(function () use ($request, $data) {
            // معالجة رفع الصورة إذا وجدت
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('features', 'public');
            }

            $feature = Feature::create($data);

            return response()->json([
                'status' => 'success',
                'message' => 'تمت إضافة الميزة بنجاح',
                'data' => $feature
            ], 201);
        });
    }

    /**
     * تحديث ميزة موجودة
     */
    public function update(Request $request, Feature $feature)
    {
        $data = $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // حذف الصورة القديمة من التخزين لتوفير المساحة
            if ($feature->image) {
                Storage::disk('public')->delete($feature->image);
            }
            // تخزين الصورة الجديدة
            $data['image'] = $request->file('image')->store('features', 'public');
        }

        $feature->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث الميزة بنجاح',
            'data' => $feature
        ]);
    }

    /**
     * حذف ميزة
     */
    public function destroy(Feature $feature)
    {
        // حذف الصورة المرتبطة من التخزين قبل حذف السجل
        if ($feature->image) {
            Storage::disk('public')->delete($feature->image);
        }

        $feature->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف الميزة بنجاح'
        ]);
    }
}