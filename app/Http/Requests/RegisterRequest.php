<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\ArabicQuadName;
use App\Rules\EgyptianNationalId;
use App\Rules\EgyptianPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // توحيد الأرقام وإزالة الشرطات والمسافات
        foreach (['phone', 'father_phone', 'mother_phone', 'national_id'] as $field) {
            if ($this->filled($field)) {
                $this->merge([$field => preg_replace('/[\s\-]/', '', $this->convertArabicDigits((string) $this->input($field)))]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', new ArabicQuadName],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', new EgyptianPhone, 'unique:users,phone'],
            'father_phone' => ['required', new EgyptianPhone, 'different:phone'],
            'mother_phone' => ['required', new EgyptianPhone, 'different:phone', 'different:father_phone'],
            'national_id' => ['required', new EgyptianNationalId, 'unique:users,national_id'],
            'governorate' => ['required', 'string', Rule::in(User::GOVERNORATES)],
            'school' => ['required', 'string', 'max:255'],
            'academic_year' => ['required', 'integer', Rule::in([1, 2, 3])],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'id_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'الاسم رباعي',
            'email' => 'البريد الإلكتروني',
            'phone' => 'رقم موبايل الطالب',
            'father_phone' => 'رقم موبايل الأب',
            'mother_phone' => 'رقم موبايل الأم',
            'national_id' => 'الرقم القومي',
            'governorate' => 'المحافظة',
            'school' => 'المدرسة',
            'academic_year' => 'الصف الدراسي',
            'gender' => 'النوع',
            'id_photo' => 'صورة البطاقة / الكارنيه',
            'password' => 'كلمة المرور',
        ];
    }

    public function messages(): array
    {
        return [
            'father_phone.different' => 'رقم موبايل الأب يجب أن يختلف عن رقم الطالب.',
            'mother_phone.different' => 'رقم موبايل الأم يجب أن يختلف عن رقم الطالب ورقم الأب.',
            'phone.unique' => 'رقم الموبايل مسجل بالفعل.',
            'email.unique' => 'البريد الإلكتروني مسجل بالفعل.',
            'national_id.unique' => 'الرقم القومي مسجل بالفعل.',
        ];
    }

    protected function convertArabicDigits(string $value): string
    {
        return strtr($value, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }
}
