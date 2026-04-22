<?php
namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Requests\Task\UpdateTaskAnswerRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;


class TaskController extends Controller
{
    // 📌 إنشاء تاسك جديد (من الداشبورد)

public function store(StoreTaskRequest $request)
{
    $task = Task::create($request->validated());

    return response()->json([
        'status'  => true,
        'message' => 'تم إنشاء التاسك بنجاح ✅',
        'data'    => $task,
    ]);
}

    // 📄 عرض كل التاسكات لجروب معين
    public function index(Request $request)
    {
        $query = Task::with('group');

        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        return response()->json([
            'status' => true,
            'data' => $query->latest()->get(),
        ]);
    }
    
    
    public function show(Task $task)
    {
        $task->load('group'); // يجيب معاه بيانات الجروب
        return response()->json([
            'status' => true,
            'data' => $task,
        ]);
    }
    

public function update(UpdateTaskRequest $request, Task $task)
{
    $task->update($request->validated());

    return response()->json([
        'status'  => true,
        'message' => 'تم تحديث بيانات التاسك بنجاح ✅',
        'data'    => $task,
    ]);
}


    /**
     * 🗑️ حذف تاسك
     */
 public function destroy(Task $task)
{
    $user = auth()->user();

    if ($user->role !== 'owner') {
        return response()->json([
            'status'  => false,
            'message' => 'غير مصرح لك بحذف هذا التاسك.',
        ], 403);
    }

    $task->delete();

    return response()->json([
        'status'  => true,
        'message' => 'تم حذف التاسك بنجاح 🗑️',
    ]);
}

    

    // ✏️ تحديث إجابة (من الطلاب)
   public function updateAnswer(UpdateTaskAnswerRequest $request, Task $task)
{
    $task->update([
        'answer' => $request->answer,
        'submitted_at' => now(),
    ]);

    return response()->json([
        'status' => true,
        'message' => 'تم إرسال الإجابة بنجاح ✅',
        'data' => $task,
    ]);
}
}
