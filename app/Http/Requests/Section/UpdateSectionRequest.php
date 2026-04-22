<?php

namespace App\Http\Requests\Section;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Auth\Access\AuthorizationException;

class UpdateSectionRequest extends FormRequest
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

    public function rules(): array
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

    public function messages(): array
    {
        return [
            'sub_category_id.exists'   => 'التصنيف الفرعي غير موجود.',
            'name.string'              => 'اسم الكورس يجب أن يكون نصًا.',
            'name.max'                 => 'اسم الكورس لا يجب أن يزيد عن 255 حرفًا.',
            'type.in'                  => 'نوع الكورس يجب أن يكون online أو offline فقط.',
            'price.numeric'            => 'السعر يجب أن يكون رقمًا.',
        ];
    }
}
