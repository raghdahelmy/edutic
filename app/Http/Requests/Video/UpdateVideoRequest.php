<?php

namespace App\Http\Requests\Video;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Auth\Access\AuthorizationException;


class UpdateVideoRequest extends FormRequest
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
        'section_id'  => 'sometimes|exists:sections,id',
        'name'        => 'sometimes|string|max:255',
        'description' => 'nullable|string',
        'video_file'  => 'nullable|file|mimes:mp4,mov,ogg,qt|max:2000000',
        'duration'    => 'nullable|string',
        'scheduled_at' => 'nullable|date_format:Y-m-d H:i',
    ];
}

public function messages(): array
{
    return [
        // القسم
        'section_id.exists' => 'القسم المحدد غير موجود في سجلاتنا.',
        
        // الاسم
        'name.string' => 'اسم الفيديو يجب أن يكون نصاً.',
        'name.max' => 'اسم الفيديو لا يجب أن يتجاوز 255 حرفاً.',
        
        // الوصف (اختياري لكن يفضل وضع قاعدة له لو وجد)
        'description.string' => 'الوصف يجب أن يكون نصاً.',
        
        // ملف الفيديو
        'video_file.file' => 'يجب اختيار ملف فيديو صالح.',
        'video_file.mimes' => 'تنسيق الفيديو يجب أن يكون أحد الأنواع التالية: mp4, mov, ogg, qt.',
        'video_file.max' => 'حجم الفيديو كبير جداً، الحد الأقصى هو 20 ميجابايت.',
        
        // المدة
        'duration.string' => 'مدة الفيديو يجب أن تكون بصيغة نصية (مثلاً 10:30).',
        
        // التاريخ والوقت
        'scheduled_at.date_format' => 'تنسيق التاريخ والوقت غير صحيح، التنسيق المطلوب هو YYYY-MM-DD HH:MM (مثال: 2024-12-30 15:30).',
    ];
}
}
