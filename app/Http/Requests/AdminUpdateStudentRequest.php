<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\ArabicQuadName;
use App\Rules\EgyptianNationalId;
use App\Rules\EgyptianPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * تعديل بيانات الطالب من لوحة التحكم — نفس قواعد التسجيل مع تجاهل
 * الطالب الحالي في فحوص التفرد (البريد/الموبايل/الرقم القومي).
 */
class AdminUpdateStudentRequest extends FormRequest
{
    /** أخطاء هذا النموذج في حقيبة مستقلة حتى لا تختلط بنماذج الصفحة الأخرى */
    protected $errorBag = 'editStudent';

    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
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
        /** @var User $student */
        $student = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255', new ArabicQuadName],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($student->id)],
            'phone' => ['required', new EgyptianPhone, Rule::unique('users', 'phone')->ignore($student->id)],
            'father_phone' => ['nullable', new EgyptianPhone, 'different:phone'],
            'mother_phone' => ['nullable', new EgyptianPhone, 'different:phone', 'different:father_phone'],
            'national_id' => ['nullable', new EgyptianNationalId, Rule::unique('users', 'national_id')->ignore($student->id)],
            'governorate' => ['nullable', 'string', Rule::in(User::GOVERNORATES)],
            'school' => ['nullable', 'string', 'max:255'],
            'academic_year' => ['nullable', 'integer', Rule::in(array_keys(User::YEARS))],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'status' => ['required', Rule::in(array_keys(User::STATUSES))],
            'center_id' => ['nullable', 'string', 'max:30'],
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
            'status' => 'حالة الحساب',
            'center_id' => 'ID طالب السنتر',
        ];
    }

    public function messages(): array
    {
        return [
            'father_phone.different' => 'رقم موبايل الأب يجب أن يختلف عن رقم الطالب.',
            'mother_phone.different' => 'رقم موبايل الأم يجب أن يختلف عن رقم الطالب ورقم الأب.',
            'phone.unique' => 'رقم الموبايل مسجل بالفعل لطالب آخر.',
            'email.unique' => 'البريد الإلكتروني مسجل بالفعل لطالب آخر.',
            'national_id.unique' => 'الرقم القومي مسجل بالفعل لطالب آخر.',
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
