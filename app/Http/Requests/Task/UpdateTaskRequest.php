<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Auth\Access\AuthorizationException;

class UpdateTaskRequest extends FormRequest

{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
   $user = auth()->user();
        return $user && $user->role === 'owner';
    }
      protected function failedAuthorization()
    {
        throw new AuthorizationException('غير مصرح لك بتعديل هذا التاسك ❌ فقط المدرسين يمكنهم ذلك.');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'group_id'    => 'sometimes|exists:groups,id',
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ];
    }

    /**
     * الرسائل المخصصة للأخطاء
     */
    public function messages(): array
    {
        return [
            'group_id.exists' => 'الجروب المحدد غير موجود.',
            'name.string'     => 'اسم التاسك يجب أن يكون نصًا.',
            'name.max'        => 'اسم التاسك لا يجب أن يتجاوز 255 حرفًا.',
        ];
    }
}
