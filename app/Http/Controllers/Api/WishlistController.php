<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        // جلب الكورسات الموجودة في مفضلة المستخدم الحالي
        $wishlist = $user->wishlist()->with(['teacher', 'path'])->get();

        return response()->json([
            'status' => true,
            'data'   => $wishlist
        ]);
    }

    public function toggleWishlist(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id'
        ]);

        $user = auth()->user();
        $courseId = $request->course_id;

        // دالة toggle تقوم بالإضافة إذا لم يكن موجوداً، والحذف إذا كان موجوداً
        $status = $user->wishlist()->toggle($courseId);

        $attached = count($status['attached']) > 0;

        return response()->json([
            'status' => true,
            'message' => $attached ? 'تمت الإضافة للمفضلة' : 'تم الحذف من المفضلة',
            'is_favorite' => $attached
        ]);
    }
}