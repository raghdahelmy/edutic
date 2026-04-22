<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskAnswerRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true;
    }


     
    public function rules(): array
    {
        return [
            'answer' => 'required|string',
        ];
    }
}
