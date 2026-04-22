<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reply;
use Illuminate\Support\Facades\Auth;

class ReplyController extends Controller
{
    public function index()
    {
        $replies = Reply::with(['user:id,name', 'comment'])->latest()->get();
        return response()->json($replies);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'يجب تسجيل الدخول أولاً.'], 401);
        }

        $request->validate([
            'comment_id' => 'required|exists:comments,id',
            'reply_text' => 'required|string|max:1000',
        ]);

        $reply = Reply::create([
            'comment_id' => $request->comment_id,
            'user_id'    => $user->id,
            'reply_text' => $request->reply_text,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'تم إضافة الرد بنجاح.',
            'data'    => $reply,
        ], 201);
    }

    public function show($id)
    {
        $reply = Reply::with(['user:id,name', 'comment'])->findOrFail($id);
        return response()->json($reply);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $reply = Reply::findOrFail($id);

        if (!$user || ($reply->user_id !== $user->id && $user->role !== 'admin')) {
            return response()->json([
                'status'  => false,
                'message' => 'غير مصرح لك بتعديل هذا الرد.',
            ], 403);
        }

        $request->validate([
            'reply_text' => 'required|string|max:1000',
        ]);

        $reply->update(['reply_text' => $request->reply_text]);

        return response()->json([
            'status'  => true,
            'message' => 'تم تعديل الرد بنجاح.',
            'data'    => $reply,
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $reply = Reply::findOrFail($id);

        if (!$user || ($reply->user_id !== $user->id && $user->role !== 'admin')) {
            return response()->json([
                'status'  => false,
                'message' => 'غير مصرح لك بحذف هذا الرد.',
            ], 403);
        }

        $reply->delete();

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف الرد بنجاح.',
        ]);
    }
}
