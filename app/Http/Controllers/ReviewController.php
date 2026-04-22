<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // ✅ عرض كل الريفيوهات (اختياري - ممكن للإدمن)
    public function index()
    {
        $reviews = Review::with(['user', 'course'])->latest()->get();
        return response()->json($reviews);
    }

    // ✅ تخزين تقييم جديد
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'rating'    => 'required|integer|min:1|max:5',
            'review'    => 'nullable|string',
        ]);

        $user = Auth::user();

        // منع التكرار
        $existing = Review::where('course_id', $request->course_id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json([
                'status'  => false,
                'message' => 'لقد قمت بتقييم هذا الكورس مسبقًا.',
            ], 400);
        }

        // إنشاء الريفيو
        $review = Review::create([
            'course_id' => $request->course_id,
            'user_id'   => $user->id,
            'rating'    => $request->rating,
            'review'    => $request->review,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'تم إضافة التقييم بنجاح.',
            'data'    => $review,
        ]);
    }

    // ✅ عرض ريفيو واحد
 public function show($id)
{
    $review = Review::with(['user', 'course'])->findOrFail($id);

    return response()->json($review);
}


    // ✅ تعديل التقييم (فقط لصاحبه)
  public function update(Request $request, $id)
{
    $user = Auth::user();

    // جلب الريفيو بناءً على الـ id
    $review = Review::findOrFail($id);

    // التحقق إن المستخدم هو صاحب الريفيو
    if ($review->user_id != $user->id) {
        return response()->json(['message' => 'غير مصرح لك بتعديل هذا التقييم.'], 403);
    }

    // التحقق من البيانات
    $validated = $request->validate([
        'rating' => 'required|integer|min:1|max:5',
        'review' => 'nullable|string',
    ]);

    // التحديث
    $review->update($validated);

    return response()->json([
        'status'  => true,
        'message' => 'تم تعديل التقييم بنجاح.',
        'data'    => $review,
    ]);
}


    // ✅ حذف التقييم (فقط لصاحبه)
    public function destroy($id)
{
    $user = Auth::user();

    // جلب الريفيو بناءً على الـ id
    $review = Review::findOrFail($id);

    // التحقق إن المستخدم هو صاحب الريفيو
    if ($review->user_id != $user->id) {
        return response()->json(['message' => 'غير مصرح لك بحذف هذا التقييم.'], 403);
    }

    // حذف الريفيو
    $review->delete();

    return response()->json([
        'status'  => true,
        'message' => 'تم حذف التقييم بنجاح.',
    ]);
}

}
