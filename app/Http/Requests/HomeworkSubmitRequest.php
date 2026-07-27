<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HomeworkSubmitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answer_text' => ['nullable', 'string', 'max:10000', 'required_without:file'],
            'file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240', 'required_without:answer_text'],
        ];
    }

    public function messages(): array
    {
        return [
            'answer_text.required_without' => 'اكتب إجابتك أو ارفع صورة الحل.',
            'file.required_without' => 'اكتب إجابتك أو ارفع صورة الحل.',
        ];
    }
}
