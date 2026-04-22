<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Section;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use App\Http\Requests\Section\UpdateSectionRequest;
use App\Http\Requests\Section\StoreSectionRequest;

class SectionController extends Controller
{
    public function index()
    {
        return response()->json(Section::with('course', 'videos')->get());
    }

    public function store(StoreSectionRequest $request)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['owner', 'admin'])) {
            return response()->json([
                'status'  => false,
                'message' => 'غير مصرح لك بإضافة أقسام.',
            ], 403);
        }

        $validated = $request->validated();

        if ($user->role === 'owner') {
            $course = Course::where('id', $validated['course_id'])
                            ->where('user_id', $user->id)
                            ->first();

            if (!$course) {
                return response()->json([
                    'status'  => false,
                    'message' => 'هذا الكورس لا يخصك، لا يمكنك إضافة أقسام له.',
                ], 403);
            }
        }

        $section = Section::create($validated);

        return response()->json([
            'status'  => true,
            'message' => 'تم إنشاء القسم بنجاح.',
            'data'    => $section,
        ], 201);
    }

    public function show($id)
    {
        $section = Section::with([
            'videos',
            'course' => function($query) {
                $query->select('id', 'name', 'type');
            }
        ])->findOrFail($id);

        return response()->json($section);
    }

    public function update(UpdateSectionRequest $request, $id)
    {
        $user = Auth::user();
        $section = Section::findOrFail($id);
        $validated = $request->validated();

        if (!in_array($user->role, ['owner', 'admin'])) {
            return response()->json(['status' => false, 'message' => 'غير مصرح لك بالتعديل.'], 403);
        }

        if ($user->role === 'owner') {
            $course = Course::where('id', $section->course_id)
                            ->where('user_id', $user->id)
                            ->first();

            if (!$course) {
                return response()->json([
                    'status'  => false,
                    'message' => 'هذا القسم لا يتبع كورساتك، لا يمكنك تعديله.',
                ], 403);
            }
        }

        $section->update($validated);

        return response()->json([
            'status'  => true,
            'message' => 'تم تعديل القسم بنجاح.',
            'data'    => $section,
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $section = Section::findOrFail($id);

        if ($user->role === 'owner') {
            $course = Course::where('id', $section->course_id)
                            ->where('user_id', $user->id)
                            ->first();
            if (!$course) {
                return response()->json(['status' => false, 'message' => 'غير مصرح لك بحذف هذا القسم.'], 403);
            }
        } elseif ($user->role !== 'admin') {
            return response()->json(['status' => false, 'message' => 'غير مصرح لك بالحذف.'], 403);
        }

        $section->delete();
        return response()->json(['status' => true, 'message' => 'تم الحذف بنجاح.']);
    }
}
