<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * ✅ تسجيل مستخدم جديد
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:owner,student',
            'image'    => 'nullable|image|max:2048',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('users', 'public');
        }
    $status = $request->role === 'student' ? 'approved' : 'pending';

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
             'password' => $request->password, 
            'role'     => $request->role,
            'image'    => $imagePath ? url('public/storage/' . $imagePath) : null,
                  'status'   => $status,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'تم التسجيل بنجاح',
            'user'    => $user,
        
        ], 201);
    }

    /**
     * ✅ تسجيل الدخول
     */
 public function login(Request $request)
{
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required|string',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['البريد الإلكتروني أو كلمة المرور غير صحيحة.'],
        ]);
    }

    if ($user->status === 'pending') {
        return response()->json([
            'status'  => false,
            'message' => 'حسابك قيد المراجعة من قبل الإدارة. سيتم إشعارك عند الموافقة.',
        ], 403);
    }

    if ($user->status === 'rejected') {
        return response()->json([
            'status'  => false,
            'message' => 'تم رفض طلب التسجيل. يرجى التواصل مع الإدارة.',
        ], 403);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'status'  => true,
        'message' => 'تم تسجيل الدخول بنجاح',
        'user'    => $user,
        'token'   => $token,
    ]);
}


    /**
     * ✅ عرض بيانات المستخدم الحالي
     */
    public function profile(Request $request)
    {
        return response()->json([
            'status' => true,
            'user'   => $request->user(),
        ]);
    }

public function updateStatus(Request $request, $id)
{
    // ✅ تحقق أن المستخدم الحالي هو الأدمن فقط
    $admin = auth()->user();
    if (!$admin || $admin->role !== 'admin') {
        return response()->json([
            'status'  => false,
            'message' => 'غير مصرح لك بتنفيذ هذا الإجراء. فقط الأدمن يمكنه تعديل الحالة.',
        ], 403);
    }

    // ✅ التحقق من البيانات المرسلة
    $request->validate([
        'status' => 'required|in:pending,approved,rejected',
    ]);

    // ✅ تحديث حالة المستخدم
    $user = User::findOrFail($id);
    $user->status = $request->status;
    $user->save();

    return response()->json([
        'status'  => true,
        'message' => 'تم تحديث حالة المستخدم بنجاح',
        'user'    => $user,
    ]);
}

public function updateProfile(Request $request)
{
    $user = auth()->user();

    // ✅ التحقق من البيانات
    $request->validate([
        'name'     => 'sometimes|string|max:255',
        'email'    => 'sometimes|email|unique:users,email,' . $user->id,
        'role'     => 'sometimes|in:admin,owner,student',
        'image'    => 'nullable|image|max:2048',
        'phone'    => 'sometimes|string|unique:users,phone,' . $user->id . '|max:20',
        'password' => 'sometimes|nullable|string|min:8',
    ]);

    // ✅ تحديث الصورة لو تم رفعها
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('users', 'public');
        $user->image = url('public/storage/' . $imagePath);
    }


    // ✅ تحديث الحقول العادية وكلمة المرور
    // بما أن لديك Mutator في الموديل، يمكنك إضافة 'password' للمصفوفة مباشرة
    $fields = ['name', 'email', 'phone', 'role', 'password'];

    foreach ($fields as $field) {
        if ($request->filled($field)) {
            // هنا الموديل سيتولى عملية الـ Hash تلقائياً عند تنفيذ هذا السطر لحقل الـ password
            $user->$field = $request->$field;
        }
    }

    $user->save();

    return response()->json([
        'status'  => true,
        'message' => 'تم تحديث الملف الشخصي بنجاح',
        'user'    => $user,
    ]);
}



    /**
     * ✅ تسجيل الخروج
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'تم تسجيل الخروج بنجاح',
        ]);
    }
    
public function getAllUsers(Request $request)
{
    // ✅ السماح للأدمن فقط
    $admin = auth()->user();
    if (!$admin || $admin->role !== 'admin') {
        return response()->json([
            'status'  => false,
            'message' => 'غير مصرح لك بعرض المستخدمين. هذا الإجراء مخصص للأدمن فقط.',
        ], 403);
    }

    // ✅ استقبال الفلاتر والبحث من الطلب
    $role     = $request->query('role');     // مثال: user, manager, admin
    $status   = $request->query('status');   // مثال: active, inactive
    $search   = $request->query('search');   // مثال: اسم أو إيميل
    $perPage  = $request->query('per_page', 10); // عدد النتائج في الصفحة الافتراضية

    // ✅ بناء الاستعلام
    $query = User::query();

    // ✅ فلترة حسب الدور
    if (!empty($role)) {
        $query->where('role', $role);
    }

    // ✅ فلترة حسب الحالة
    if (!empty($status)) {
        $query->where('status', $status);
    }

    // ✅ البحث في الاسم أو البريد الإلكتروني أو رقم الجوال
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    // ✅ استثناء الأدمن (اختياري)
    $query->where('role', '!=', 'admin');

    // ✅ الترتيب الأحدث أولاً
    $query->orderBy('created_at', 'desc');

    // ✅ تطبيق الـ pagination
    $users = $query->paginate($perPage);

    // ✅ الإرجاع
    return response()->json([
        'status'  => true,
        'message' => 'تم جلب المستخدمين بنجاح',
        'data'    => $users,
    ], 200);
}

    /**
     * ✅ 1. إرسال كود استعادة كلمة المرور (Forget Password)
     */
public function forgotPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ]);

    $otp = rand(100000, 999999);

    DB::table('password_reset_tokens')->updateOrInsert(
        ['email' => $request->email],
        [
            'token' => $otp,
            'created_at' => Carbon::now()
        ]
    );

    Mail::send('emails.forgot_password', ['otp' => $otp], function ($message) use ($request, $otp) {
        $message->to($request->email)
            ->subject($otp . ' هو رمز التحقق الخاص بك')
            ->from('academy@zh-innovation.com', 'NeoCampus');
    });

    return response()->json([
        'status' => true,
        'message' => 'تم إرسال الكود بنجاح',
    ]);
}

    /**
     * ✅ 2. إعادة تعيين كلمة المرور (Reset Password)
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|exists:users,email',
            'otp'      => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        $resetData = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->otp)
            ->where('created_at', '>', Carbon::now()->subMinutes(60))
            ->first();

        if (!$resetData) {
            return response()->json([
                'status'  => false,
                'message' => 'كود التحقق غير صحيح أو انتهت صلاحيته.',
            ], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = $request->password; // الـ Mutator في الموديل سيقوم بالـ hashing
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'status'  => true,
            'message' => 'تم إعادة تعيين كلمة المرور بنجاح.',
        ]);
    }

public function createUser(Request $request)
{
    $owner = auth()->user();
    if (!$owner || $owner->role !== 'owner') {
        return response()->json([
            'status'  => false,
            'message' => 'غير مصرح لك. فقط الـ Owner يمكنه إنشاء مستخدمين.',
        ], 403);
    }

    $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users',
        'password' => 'required|string|min:6',
        'role'     => 'required|in:admin,student',
        'phone'    => 'nullable|string|unique:users,phone|max:20',
        'image'    => 'nullable|image|max:2048',
    ]);

    $imagePath = null;
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('users', 'public');
    }

    $user = User::create([
        'name'     => $request->name,
        'email'    => $request->email,
        'password' => $request->password,
        'role'     => $request->role,
        'phone'    => $request->phone,
        'image'    => $imagePath ? url('public/storage/' . $imagePath) : null,
        'status'   => 'approved',
    ]);

    return response()->json([
        'status'  => true,
        'message' => 'تم إنشاء المستخدم بنجاح',
        'user'    => $user,
    ], 201);
}

public function assignRole(Request $request, $id)
{
    $owner = auth()->user();
    if (!$owner || $owner->role !== 'owner') {
        return response()->json([
            'status'  => false,
            'message' => 'غير مصرح لك. فقط الـ Owner يمكنه تغيير الأدوار.',
        ], 403);
    }

    $request->validate([
        'role' => 'required|in:admin,student',
    ]);

    $user = User::findOrFail($id);
    $user->role = $request->role;
    $user->save();

    return response()->json([
        'status'  => true,
        'message' => 'تم تغيير دور المستخدم بنجاح',
        'user'    => $user,
    ]);
}

}
