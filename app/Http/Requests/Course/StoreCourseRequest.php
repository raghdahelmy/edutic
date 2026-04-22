<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();

        // لو المستخدم مش Teacher
     if (!$user || !in_array($user->role, ['owner', 'admin'])) {
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

   

    public function rules(): array
    {
        return [
            'sub_category_id' => 'required|exists:sub_categories,id',
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'image'           => 'nullable|image',
            'short_video'     => 'nullable|mimetypes:video/mp4,video/mpeg,video/quicktime|max:204800',
            'type'            => 'required|in:online,offline',
            'price'           => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'sub_category_id.required' => 'يجب اختيار تصنيف فرعي.',
            'sub_category_id.exists'   => 'التصنيف الفرعي غير موجود.',
            'name.required'            => 'اسم الكورس مطلوب.',
            'name.max'                 => 'اسم الكورس لا يجب أن يزيد عن 255 حرفًا.',
            'type.in'                  => 'نوع الكورس يجب أن يكون online أو offline فقط.',
            'price.required'           => 'السعر مطلوب.',
            'price.numeric'            => 'السعر يجب أن يكون رقمًا.',
        ];
    }
}
