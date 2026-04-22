<?php

namespace App\Http\Controllers;

use App\Models\MainCategory;
use Illuminate\Http\Request;

class MainCategoryController extends Controller
{
    public function index()
    {
        return response()->json(MainCategory::all());
    }

    public function show($id)
    {
        $category = MainCategory::findOrFail($id);
        return response()->json($category);
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('main_categories', 'public');
        }

        $category = MainCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $path ? url('public/storage/' . $path) : null,
        ]);

        return response()->json($category, 201);
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
        $category = MainCategory::findOrFail($id);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('main_categories', 'public');
            $category->image = url('public/storage/' . $path);
        }

        $category->update($request->only(['name', 'description']));

        return response()->json($category);
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
        $category = MainCategory::findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
