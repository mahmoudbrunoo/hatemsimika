<?php

namespace App\Http\Requests;

/** رد/تعليق على سؤال درس — نفس قواعد السؤال (نص أو صوت + صورة اختيارية) */
class QaReplyRequest extends QaThreadRequest
{
    public function attributes(): array
    {
        return ['body' => 'نص الرد', 'image' => 'الصورة المرفقة', 'audio' => 'الرسالة الصوتية'];
    }

    public function messages(): array
    {
        return [
            'body.required_without' => 'اكتب نص الرد أو سجّل رسالة صوتية.',
            'audio.mimetypes' => 'الرسالة الصوتية لازم تكون ملف صوتي (mp3 / m4a / wav / webm / ogg).',
        ];
    }
}
