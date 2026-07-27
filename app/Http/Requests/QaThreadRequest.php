<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QaThreadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:5', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function attributes(): array
    {
        return ['body' => 'نص السؤال', 'image' => 'صورة توضيحية'];
    }
}
