<?php

namespace App\Http\Requests\Video;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Auth\Access\AuthorizationException;

class StoreVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
$user = auth()->user();

        // لو المستخدم مش Teacher
   if (!$user || $user->role !== 'owner') {
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
        );    }

public function rules(): array
{
    return [
        'section_id'  => 'required|exists:sections,id',
        'name'        => 'required|string|max:255',
        'description' => 'nullable|string',
        'video_file'  => 'required|file|mimes:mp4,mov,ogg,qt|max:2000000',
        'duration'    => 'required|string',
        'scheduled_at' => 'nullable|date_format:Y-m-d H:i',
    ];
}

public function messages(): array
{
    return [
        // القسم
        'section_id.required' => 'يجب اختيار القسم المرتبط بهذا الفيديو.',
        'section_id.exists'   => 'القسم المختار غير موجود في قاعدة البيانات.',

        // اسم الفيديو
        'name.required' => 'اسم الفيديو حقل إلزامي.',
        'name.string'   => 'اسم الفيديو يجب أن يكون نصًا.',
        'name.max'      => 'اسم الفيديو لا يجب أن يتجاوز 255 حرفًا.',

        // الوصف
        'description.string' => 'الوصف يجب أن يكون نصًا.',

        // ملف الفيديو (File Upload)
        'video_file.file'  => 'يجب أن يكون الملف المرفوع فيديو صالحًا.',
        'video_file.mimes' => 'تنسيقات الفيديو المدعومة هي: mp4, mov, ogg, qt.',
        'video_file.max'   => 'حجم الفيديو كبير جدًا، يرجى رفع ملف أصغر.',

        // مدة الفيديو
        'duration.required' => 'مدة الفيديو مطلوبة.',
        'duration.string'   => 'يجب إدخال مدة الفيديو بشكل نصي صحيح.',

        // جدولة النشر (Date & Time)
        'scheduled_at.date_format' => 'تنسيق التاريخ والوقت غير صحيح، التنسيق المطلوب هو YYYY-MM-DD HH:MM (مثال: 2024-12-30 15:30).',
    ];
}
}
