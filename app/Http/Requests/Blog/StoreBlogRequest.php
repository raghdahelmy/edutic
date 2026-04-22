<?php

namespace App\Http\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Auth\Access\AuthorizationException;


class StoreBlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();

        // مسموح فقط للمدرس أو الأدمن
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
                'message' => 'غير مصرح لك بإنشاء المقالات.',
            ], 403)
        );
    }

    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'imgSrc'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'user_id'     => 'nullable|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'   => 'عنوان المقال مطلوب.',
            'title.string'     => 'عنوان المقال يجب أن يكون نصًا.',
            'title.max'        => 'عنوان المقال لا يجب أن يتجاوز 255 حرفًا.',
            'imgSrc.image'     => 'يجب أن يكون الملف صورة.',
            'imgSrc.mimes'     => 'يُسمح فقط بصيغ الصور: jpeg, png, jpg, gif.',
            'user_id.exists'   => 'المستخدم غير موجود.',
        ];
    }
}
