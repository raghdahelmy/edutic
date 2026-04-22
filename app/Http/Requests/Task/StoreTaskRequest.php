<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Auth\Access\AuthorizationException;


class StoreTaskRequest extends FormRequest
{
  public function authorize(): bool
    {
        $user = auth()->user();

        // ✅ فقط المدرسين مسموح لهم بإنشاء التاسكات
        return $user && $user->role === 'owner';
    }

    /**
     * في حالة الفشل في التفويض
     */
    protected function failedAuthorization()
    {
        throw new AuthorizationException('غير مصرح لك بإنشاء التاسك ❌ فقط المدرسين يمكنهم ذلك.');
    }
    public function rules(): array
    {
        return [
            'group_id' => 'required|exists:groups,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ];
    }

    /**
     * الرسائل المخصصة للأخطاء (اختياري)
     */
    public function messages(): array
    {
        return [
            'group_id.required' => 'يجب اختيار الجروب المرتبط بالتاسك.',
            'group_id.exists' => 'الجروب المختار غير موجود.',
            'name.required' => 'اسم التاسك مطلوب.',
            'name.max' => 'اسم التاسك لا يجب أن يتجاوز 255 حرفًا.',
        ];
    }
}
