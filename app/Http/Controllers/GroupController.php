<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    // 🧾 إنشاء جروب
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'course_id' => 'required|exists:courses,id',
            'users' => 'array',
            'users.*' => 'exists:users,id',
        ]);

        $group = Group::create([
            'name' => $validated['name'],
            'course_id' => $validated['course_id'],
        ]);

        if (!empty($validated['users'])) {
            $group->users()->attach($validated['users']);
        }

        return response()->json(['message' => 'Group created successfully', 'group' => $group], 201);
    }
    
        // ✏️ تعديل بيانات الجروب
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'course_id' => 'sometimes|exists:courses,id',
            'users' => 'sometimes|array',
            'users.*' => 'exists:users,id',
        ]);

        $group = Group::find($id);

        if (!$group) {
            return response()->json(['status' => false, 'message' => 'الجروب غير موجود ❌'], 404);
        }

        $group->update($validated);

        if (isset($validated['users'])) {
            // استبدال المستخدمين الحاليين بالمجموعة الجديدة
            $group->users()->sync($validated['users']);
        }

        return response()->json(['status' => true, 'message' => 'تم تحديث بيانات الجروب بنجاح ✅', 'data' => $group]);
    }

    // 🗑️ حذف الجروب
    public function destroy($id)
    {
        $group = Group::find($id);

        if (!$group) {
            return response()->json(['status' => false, 'message' => 'الجروب غير موجود ❌'], 404);
        }

        // فك الارتباط مع المستخدمين قبل الحذف (اختياري)
        $group->users()->detach();

        $group->delete();

        return response()->json(['status' => true, 'message' => 'تم حذف الجروب بنجاح 🗑️']);
    }

    // 👀 عرض كل الجروبات
    public function index()
    {
        $groups = Group::with(['course', 'users'])->get();
        return response()->json($groups);
    }
    
    
    public function show($id)
{
    $group = Group::with(['course', 'users', 'tasks'])->find($id);

    if (!$group) {
        return response()->json([
            'status' => false,
            'message' => 'الجروب غير موجود ❌',
        ], 404);
    }

    return response()->json([
        'status' => true,
        'message' => 'تم جلب بيانات الجروب بنجاح ✅',
        'data' => $group,
    ]);
}

    // 🧩 إضافة تقييم أو مهمة لفرد
    public function updateMember(Request $request, $groupId, $userId)
    {
        $validated = $request->validate([
            'individual_rating' => 'nullable|numeric|min:0|max:5',
            'task' => 'nullable|string',
        ]);

        $group = Group::findOrFail($groupId);
        $group->users()->updateExistingPivot($userId, $validated);

        return response()->json(['message' => 'Member updated successfully']);
    }

    // 🌟 تقييم الجروب نفسه
    public function rateGroup(Request $request, $groupId)
    {
        $validated = $request->validate([
            'group_rating' => 'required|numeric|min:0|max:5',
        ]);

        $group = Group::findOrFail($groupId);
        $group->update(['group_rating' => $validated['group_rating']]);

        return response()->json(['message' => 'Group rated successfully']);
    }
}
