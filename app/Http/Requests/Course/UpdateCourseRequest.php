<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
{
    public function authorize()
    {
        $user = auth()->user();

        // ✅ فقط المدرس المالك للكورس يقدر يعدله
    if (!$user || $user->role !== 'owner') {
        return false;
    }

        return true;
    }

    protected function failedAuthorization()
    {
        abort(response()->json([
            'status'  => false,
            'message' => 'غير مصرح لك بتعديل الكورسات. فقط المدرسين المالكين يمكنهم القيام بذلك.',
        ], 403));
    }

    public function rules()
    {
        return [
            'sub_category_id' => 'sometimes|exists:sub_categories,id',
            'name'            => 'sometimes|string|max:255',
            'description'     => 'nullable|string',
            'image'           => 'nullable|image',
            'short_video'     => 'nullable|mimetypes:video/mp4,video/mpeg,video/quicktime|max:204800',
            'type'            => 'sometimes|in:online,offline',
            'price'           => 'sometimes|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'sub_category_id.exists' => 'التصنيف الفرعي غير موجود.',
            'name.max'               => 'اسم الكورس لا يجب أن يزيد عن 255 حرفًا.',
            'type.in'                => 'نوع الكورس يجب أن يكون online أو offline فقط.',
            'price.numeric'          => 'السعر يجب أن يكون رقمًا.',
        ];
    }
}
