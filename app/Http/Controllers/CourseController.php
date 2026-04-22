<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Http\Requests\Course\StoreCourseRequest;
use App\Models\Subscription;
use App\Http\Requests\Course\UpdateCourseRequest;
use Illuminate\Support\Facades\Auth;



class CourseController extends Controller
{
  
public function index(Request $request)
{
 $query = Course::with(['subCategory', 'teacher','reviews.user'])
            ->withAvg('reviews', 'rating');

    if ($request->filled('search')) {
        $query->where('name', 'LIKE', "%{$request->search}%");
    }

    //  فلترة بـ category_id
    if ($request->filled('category_id')) {
        $query->whereHas('subCategory', function ($q) use ($request) {
            $q->where('main_category_id', $request->category_id);
        });
    }

    if ($request->filled('type')) {
        $query->where('type', $request->type);
    }

    // فلترة بالسعر (min & max)
    if ($request->filled('price_min')) {
        $query->where('price', '>=', $request->price_min);
    }
    if ($request->filled('price_max')) {
        $query->where('price', '<=', $request->price_max);
    }

    if ($request->filled('teacher_id')) {
        $query->where('user_id', $request->teacher_id);
    }


    // سورت (ترتيب النتائج)
   if ($request->filled('sort_by')) {
    $query->orderBy($request->sort_by, $request->dir ?? 'asc');
}

    
    $perPage = $request->query('per_page', 10);

    $courses = $query->paginate($perPage);

    return response()->json($courses);
}

   public function store(StoreCourseRequest $request)
{
    $user = auth()->user();

    $validated = $request->validated();

    if ($request->hasFile('image')) {
        $validated['image'] = $request->file('image')->store('courses', 'public');
    }

    if ($request->hasFile('short_video')) {
        $validated['short_video'] = $request->file('short_video')->store('course_videos', 'public');
    }

  
    $course = $user->courses()->create($validated);

    return response()->json([
        'status'  => true,
        'message' => 'تم إنشاء الكورس بنجاح.',
        'data'    => $course,
    ], 201);
}

public function show($id)
{
    $course = Course::with(['sections.videos','subCategory', 'teacher', 'sections', 'reviews.user'])
        ->withAvg('reviews', 'rating')
        ->findOrFail($id);

    $user = auth('sanctum')->user();
    $course->is_subscribed = false;
    $course->subscription_status = null;

    if ($user) {
        $subscription = \App\Models\Subscription::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($subscription) {
            $course->is_subscribed = true;
            $course->subscription_status = $subscription->status; 


        }
    }

    return response()->json($course);
}


    
 public function update(UpdateCourseRequest $request, $id)
{
    $user = auth()->user();

    $course = Course::findOrFail($id);

    // ✅ تأكد أن الكورس يخص المدرس الحالي
    if ($course->user_id !== $user->id) {
        return response()->json([
            'status'  => false,
            'message' => 'هذا الكورس لا يخصك، لا يمكنك تعديله.',
        ], 403);
    }

    $validated = $request->validated();

    if ($request->hasFile('image')) {
        $validated['image'] = $request->file('image')->store('courses', 'public');
    }

    if ($request->hasFile('short_video')) {
        $validated['short_video'] = $request->file('short_video')->store('course_videos', 'public');
    }

    $course->update($validated);

    return response()->json([
        'status'  => true,
        'message' => 'تم تعديل الكورس بنجاح.',
        'data'    => $course,
    ]);
}

    public function destroy($id)
    {
        $user=Auth::user();
        // ✅ تحقق من أن المستخدم Teacher فقط
        if (auth()->user()->role !== 'owner') {
            return response()->json([
                'status'  => false,
                'message' => 'غير مصرح لك بحذف الكورسات. فقط الـ Owner يمكنه القيام بذلك.',
            ], 403);
        }

        $course = Course::findOrFail($id);
        
    // ✅ تأكد إن الكورس فعلاً بتاع المدرس الحالي
    if ($course->user_id !== $user->id) {
        return response()->json([
            'status' => false,
            'message' => 'هذا الكورس لا يخصك، لا يمكنك حذفه.',
        ], 403);
    }
        $course->delete();
        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
    
    //Route special for dashboard
public function indexCourse(Request $request)
{
    $user = Auth::user();

    // 1. لو المستخدم أدمن
    if ($user->isAdmin()) { 
        // أضفنا 'teacher' هنا لجلب بيانات المدرس مع الكورس
        $query = Course::with(['subscriptions.user', 'groups', 'teacher']);

        if ($request->filled('status')) {
            $query->whereHas('subscriptions', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $courses = $query->paginate(10);

        return response()->json([
            'status' => true,
            'role' => 'admin',
            'message' => 'تم عرض الكورسات مع الاشتراكات وبيانات المعلمين',
            'courses' => $courses
        ], 200);
    }

    // 2. لو المستخدم مدرس
    if ($user->isOwner()) {
        $query = Course::with(['subscriptions.user', 'groups', 'teacher'])
            ->where('user_id', $user->id);

        if ($request->filled('status')) {
            $query->whereHas('subscriptions', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $courses = $query->paginate(10);

        return response()->json([
            'status' => true,
            'role' => 'owner',
            'message' => 'تم عرض كورسات الـ Owner مع الاشتراكات',
            'courses' => $courses
        ], 200);
    }

    return response()->json([
        'status' => false,
        'message' => 'غير مصرح لك. هذا المسار مخصص فقط للأدمن والـ Owner.',
    ], 403);
}

}
