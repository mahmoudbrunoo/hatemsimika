<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** الاسم رباعي: 4 كلمات عربية على الأقل */
class ArabicQuadName implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $words = preg_split('/\s+/u', trim((string) $value), -1, PREG_SPLIT_NO_EMPTY);

        if (count($words) < 4) {
            $fail('يجب إدخال الاسم رباعي (4 كلمات على الأقل).');

            return;
        }

        foreach ($words as $word) {
            if (! preg_match('/^[\x{0600}-\x{06FF}\x{0750}-\x{077F}]+$/u', $word)) {
                $fail('يجب كتابة الاسم باللغة العربية فقط.');

                return;
            }
        }
    }
}
