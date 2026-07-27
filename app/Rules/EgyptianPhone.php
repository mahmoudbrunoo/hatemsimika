<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** رقم موبايل مصري: يبدأ بـ 010/011/012/015 ويتكون من 11 رقم */
class EgyptianPhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $normalized = preg_replace('/[\s\-]/', '', (string) $value);

        if (! preg_match('/^01[0125][0-9]{8}$/', $normalized)) {
            $fail('يجب إدخال رقم موبايل مصري صحيح (01x-xxxx-xxxx).');
        }
    }
}
