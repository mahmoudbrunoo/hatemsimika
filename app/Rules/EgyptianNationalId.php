<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** الرقم القومي المصري: 14 رقم بصيغة صحيحة (القرن + تاريخ الميلاد + المحافظة) */
class EgyptianNationalId implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $id = (string) $value;

        if (! preg_match('/^[23][0-9]{13}$/', $id)) {
            $fail('الرقم القومي يجب أن يتكون من 14 رقم ويبدأ بـ 2 أو 3.');

            return;
        }

        $month = (int) substr($id, 3, 2);
        $day = (int) substr($id, 5, 2);

        if ($month < 1 || $month > 12 || $day < 1 || $day > 31) {
            $fail('الرقم القومي غير صحيح (تاريخ الميلاد داخل الرقم غير سليم).');
        }
    }
}
