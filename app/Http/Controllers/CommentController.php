<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function index()
    {
        $comments = Comment::with(['user:id,name', 'blog:id,title'])->latest()->get();
        return response()->json($comments);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'يجب تسجيل الدخول أولاً.'], 401);
        }

        $request->validate([
            'blog_id'      => 'required|exists:blogs,id',
            'comment_text' => 'required|string|max:1000',
        ]);

        $comment = Comment::create([
            'blog_id'      => $request->blog_id,
            'user_id'      => $user->id,
            'comment_text' => $request->comment_text,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'تم إضافة التعليق بنجاح.',
            'data'    => $comment,
        ], 201);
    }

    public function show($id)
    {
        $comment = Comment::with(['user:id,name', 'blog:id,title', 'replies.user:id,name'])->findOrFail($id);
        return response()->json($comment);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $comment = Comment::findOrFail($id);

        if (!$user || ($comment->user_id !== $user->id && $user->role !== 'admin')) {
            return response()->json([
                'status'  => false,
                'message' => 'غير مصرح لك بتعديل هذا التعليق.',
            ], 403);
        }

        $request->validate([
            'comment_text' => 'required|string|max:1000',
        ]);

        $comment->update(['comment_text' => $request->comment_text]);

        return response()->json([
            'status'  => true,
            'message' => 'تم تعديل التعليق بنجاح.',
            'data'    => $comment,
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $comment = Comment::findOrFail($id);

        if (!$user || ($comment->user_id !== $user->id && $user->role !== 'admin')) {
            return response()->json([
                'status'  => false,
                'message' => 'غير مصرح لك بحذف هذا التعليق.',
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف التعليق بنجاح.',
        ]);
    }
}
