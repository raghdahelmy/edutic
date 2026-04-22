<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'يجب تسجيل الدخول للوصول إلى هذه البيانات.',
            ], 401);
        }

        $query = Subscription::with(['user', 'course']);

        // الـ owner يشوف اشتراكات كورساته فقط
        if ($user->role === 'owner') {
            $query->whereHas('course', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->whereHas('course', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            $subscriptions = $query->latest()->paginate($request->get('per_page', 10));

            return response()->json([
                'status' => true,
                'role' => 'owner',
                'message' => 'تم عرض اشتراكات الـ Owner بنجاح.',
                'data' => $subscriptions,
            ], 200);
        }

        // الـ admin يشوف كل الاشتراكات
        if ($user->role === 'admin') {
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->whereHas('course', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            $subscriptions = $query->latest()->paginate($request->get('per_page', 10));

            return response()->json([
                'status' => true,
                'role' => 'admin',
                'message' => 'تم عرض جميع الاشتراكات بنجاح.',
                'data' => $subscriptions,
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'غير مصرح لك بعرض هذه البيانات.',
        ], 403);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'student') {
            return response()->json([
                'status' => false,
                'message' => 'فقط الطلاب يمكنهم الاشتراك في الكورسات.',
            ], 403);
        }

        $request->validate([
            'course_id'      => 'required|exists:courses,id',
            'payment_method' => 'required|in:instapay,vodafone_cash',
        ]);

        $alreadySubscribed = Subscription::where('user_id', $user->id)
            ->where('course_id', $request->course_id)
            ->exists();

        if ($alreadySubscribed) {
            return response()->json([
                'status' => false,
                'message' => 'أنت مشترك بالفعل في هذا الكورس.',
            ], 422);
        }

        $subscription = Subscription::create([
            'user_id'        => $user->id,
            'course_id'      => $request->course_id,
            'status'         => 'pending',
            'payment_method' => $request->payment_method,
        ]);

        $paymentDetails = [
            'instapay'      => ['account' => '01XXXXXXXXX', 'name' => 'اسم الحساب'],
            'vodafone_cash' => ['account' => '01XXXXXXXXX', 'name' => 'اسم الحساب'],
        ];

        return response()->json([
            'status'  => true,
            'message' => 'تم إنشاء طلب الاشتراك. يرجى إتمام الدفع ورفع الإيصال.',
            'data'    => $subscription,
            'payment' => $paymentDetails[$request->payment_method],
        ], 201);
    }

    public function uploadReceipt(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'student') {
            return response()->json([
                'status'  => false,
                'message' => 'فقط الطلاب يمكنهم رفع الإيصال.',
            ], 403);
        }

        $subscription = Subscription::findOrFail($id);

        if ($subscription->user_id !== $user->id) {
            return response()->json([
                'status'  => false,
                'message' => 'هذا الاشتراك لا يخصك.',
            ], 403);
        }

        if ($subscription->status !== 'pending') {
            return response()->json([
                'status'  => false,
                'message' => 'لا يمكن رفع إيصال لاشتراك تم تفعيله أو إلغاؤه.',
            ], 422);
        }

        $request->validate([
            'receipt' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($subscription->receipt) {
            $oldPath = str_replace(url('storage/'), '', $subscription->receipt);
            Storage::disk('public')->delete($oldPath);
        }

        $path = $request->file('receipt')->store('receipts', 'public');
        $subscription->receipt = url('storage/' . $path);
        $subscription->save();

        return response()->json([
            'status'  => true,
            'message' => 'تم رفع الإيصال بنجاح. في انتظار مراجعة الـ Owner.',
            'data'    => $subscription,
        ]);
    }

    public function updateSubscriptionStatus(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user || !in_array($user->role, ['owner', 'admin'])) {
            return response()->json([
                'status' => false,
                'message' => 'غير مصرح لك. فقط الـ Owner أو Admin يمكنهم تعديل حالة الاشتراكات.',
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,active,cancelled',
        ]);

        $subscription = Subscription::with('course')->findOrFail($id);

        if ($subscription->course->user_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'لا يمكنك تعديل اشتراك لا يخص كورساتك.',
            ], 403);
        }

        $subscription->status = $validated['status'];
        $subscription->save();

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث حالة الاشتراك بنجاح.',
            'data' => $subscription,
        ]);
    }

    public function mySubscriptions()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $subscriptions = Subscription::with('course')
            ->where('user_id', $user->id)
            ->get();

        return response()->json([
            'status' => true,
            'data' => $subscriptions,
        ]);
    }

    public function show($id)
    {
        $subscription = Subscription::with(['user', 'course'])->findOrFail($id);
        $userId = Auth::id();

        $subscription->is_subscribed = ($userId && $subscription->user_id == $userId);

        return response()->json($subscription);
    }

    public function destroy($id)
    {
        $subscription = Subscription::findOrFail($id);
        $subscription->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
