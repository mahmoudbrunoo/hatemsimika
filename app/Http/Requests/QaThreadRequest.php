<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * سؤال/تعليق على الدرس: نص فقط، نص بصورة، أو رسالة صوتية (تسجيل مباشر أو ملف مرفوع)
 * — النص إجباري فقط عند غياب الرسالة الصوتية.
 */
class QaThreadRequest extends FormRequest
{
    // تسجيلات المتصفح تُسنّف أحياناً كـ video/webm أو video/mp4 (حاويات) رغم أنها صوت فقط
    public const AUDIO_MIMETYPES = [
        'audio/webm', 'video/webm',
        'audio/ogg', 'application/ogg',
        'audio/mpeg', 'audio/mp3',
        'audio/mp4', 'audio/x-m4a', 'audio/m4a', 'video/mp4',
        'audio/aac',
        'audio/wav', 'audio/x-wav', 'audio/wave',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['nullable', 'required_without:audio', 'string', 'min:5', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'audio' => ['nullable', 'file', 'mimetypes:' . implode(',', self::AUDIO_MIMETYPES), 'max:10240'],
        ];
    }

    public function attributes(): array
    {
        return ['body' => 'نص السؤال', 'image' => 'صورة توضيحية', 'audio' => 'الرسالة الصوتية'];
    }

    public function messages(): array
    {
        return [
            'body.required_without' => 'اكتب نص السؤال أو سجّل رسالة صوتية.',
            'audio.mimetypes' => 'الرسالة الصوتية لازم تكون ملف صوتي (mp3 / m4a / wav / webm / ogg).',
        ];
    }
}
