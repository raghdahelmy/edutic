<?php

namespace App\Http\Controllers;

use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    public function index()
    {
        return response()->json(SubCategory::with('mainCategory')->get());
    }

    public function show($id)
    {
        $sub = SubCategory::with('mainCategory')->findOrFail($id);
        return response()->json($sub);
    }

    public function store(Request $request)
    {
           // ✅ تحقق من أن المستخدم أدمن
    if (auth()->user()->role !== 'admin') {
        return response()->json([
            'status'  => false,
            'message' => 'غير مصرح لك بتنفيذ هذا الإجراء. فقط الأدمن يمكنه القيام بذلك.',
        ], 403);
    }
        $request->validate([
            'main_category_id' => 'required|exists:main_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('sub_categories', 'public');
        }

        $sub = SubCategory::create([
            'main_category_id' => $request->main_category_id,
            'name' => $request->name,
            'description' => $request->description,
            'image' => $path ? url('public/storage/' . $path) : null,
        ]);

        return response()->json($sub, 201);
    }

    public function update(Request $request, $id)
    {
           // ✅ تحقق من أن المستخدم أدمن
    if (auth()->user()->role !== 'admin') {
        return response()->json([
            'status'  => false,
            'message' => 'غير مصرح لك بتنفيذ هذا الإجراء. فقط الأدمن يمكنه القيام بذلك.',
        ], 403);
    }
        $sub = SubCategory::findOrFail($id);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('sub_categories', 'public');
            $sub->image = url('public/storage/' . $path);
        }

        $sub->update($request->only(['main_category_id', 'name', 'description']));

        return response()->json($sub);
    }

    public function destroy($id)
    {
                 // ✅ تحقق من أن المستخدم أدمن
    if (auth()->user()->role !== 'admin') {
        return response()->json([
            'status'  => false,
            'message' => 'غير مصرح لك بتنفيذ هذا الإجراء. فقط الأدمن يمكنه القيام بذلك.',
        ], 403);
    }
        $sub = SubCategory::findOrFail($id);
        $sub->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
