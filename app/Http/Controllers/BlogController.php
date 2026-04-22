<?php

namespace App\Http\Controllers;

use App\Http\Requests\Blog\StoreBlogRequest;
use App\Http\Requests\Blog\UpdateBlogRequest;
use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    // عرض كل المقالات (للزوار)
    public function index()
    {
        $blogs = Blog::with('user')->latest()->get();
        return response()->json($blogs);
    }

    // ✅ الدالة التي كانت مفقودة: عرض مقال واحد بالتفصيل
    public function show($id)
    {
        $blog = Blog::with('user')->findOrFail($id);
        
        return response()->json([
            'status' => true,
            'blog'   => $blog,
        ]);
    }

    // إنشاء مقال جديد
    public function store(StoreBlogRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('imgSrc')) {
            $path = $request->file('imgSrc')->store('blogs', 'public');
            // تحسين: استخدام asset() بدلاً من url() اليدوي
            $validated['imgSrc'] = asset('storage/' . $path);
        }

        $blog = Blog::create($validated);

        return response()->json([
            'status'  => true,
            'message' => 'تم إنشاء المقال بنجاح',
            'blog'    => $blog,
        ], 201);
    }

    // تحديث مقال
    public function update(UpdateBlogRequest $request, $id)
    {
        $blog = Blog::findOrFail($id);
        $validated = $request->validated();

        if ($request->hasFile('imgSrc')) {
            $path = $request->file('imgSrc')->store('blogs', 'public');
            $validated['imgSrc'] = asset('storage/' . $path);
        } else {
            // الحفاظ على الصورة القديمة إذا لم يتم رفع صورة جديدة
            $validated['imgSrc'] = $blog->imgSrc;
        }

        $blog->update($validated);

        return response()->json([
            'status'  => true,
            'message' => 'تم تعديل المقال بنجاح',
            'blog'    => $blog,
        ]);
    }

    // حذف مقال
    public function destroy($id)
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'owner') {
            return response()->json([
                'status'  => false,
                'message' => 'غير مصرح لك بحذف المقالات. فقط الـ Owner يمكنه القيام بذلك.',
            ], 403);
        }

        $blog = Blog::findOrFail($id);
        $blog->delete();

        return response()->json(['message' => 'تم حذف المقال بنجاح']);
    }

    // عرض المقالات في لوحة التحكم (مع صلاحيات)
    public function indexAdmin(Request $request)
    {
        $user = auth()->user();
        $query = Blog::with('user');

        if ($user->isOwner()) {
            $query->where('user_id', $user->id);
        }
        elseif (!$user->isAdmin()) {
            return response()->json([
                'status'  => false,
                'message' => 'غير مصرح لك بالوصول إلى هذه البيانات.',
            ], 403);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $blogs = $query->latest()->paginate(10);

        return response()->json([
            'status' => true,
            'blogs'  => $blogs,
        ]);
    }
}