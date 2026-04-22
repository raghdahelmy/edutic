<?php

namespace App\Http\Requests\Section;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Auth\Access\AuthorizationException;


class StoreSectionRequest extends FormRequest
{
     public function authorize(): bool
    {
        $user = auth()->user();

        // مسموح فقط للمدرس (teacher)
  if (!$user || !in_array($user->role, ["owner", "admin"])) {
            return false;
        }

        return true;
    }

   
 protected function failedAuthorization()
    {
        throw new AuthorizationException(
            response()->json([
                'status'  => false,
                'message' => 'غير مصرح لك بتعديل الكورسات. فقط المدرسين يمكنهم القيام بذلك.',
            ], 403)
        );
    }
    public function rules()
    {
        return [
            'course_id' => 'required|exists:courses,id',
            'name'      => 'required|string|max:255',
        'description'=> 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'course_id.required' => 'يجب اختيار الكورس المرتبط بهذا القسم.',
            'course_id.exists'   => 'الكورس غير موجود.',
            'name.required'      => 'اسم القسم مطلوب.',
            'name.string'        => 'اسم القسم يجب أن يكون نصًا.',
                  'description.string' => 'الوصف يجب أن يكون نصًا.',

            'name.max'           => 'اسم القسم لا يجب أن يتجاوز 255 حرفًا.',
        ];
    }
}
