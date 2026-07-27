<?php

namespace App\Http\Requests;

use App\Rules\EgyptianPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManualPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['required', Rule::in(['manual_vodafone', 'manual_instapay', 'wallet'])],
            'receipt' => ['required_unless:payment_method,wallet', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'sender_phone' => ['nullable', new EgyptianPhone],
            'coupon_code' => ['nullable', 'string', 'max:40'],
        ];
    }

    public function attributes(): array
    {
        return [
            'payment_method' => 'طريقة الدفع',
            'receipt' => 'صورة إيصال التحويل',
            'sender_phone' => 'الرقم المحول منه',
            'coupon_code' => 'كود الخصم',
        ];
    }

    public function messages(): array
    {
        return [
            'receipt.required_unless' => 'ارفع صورة إيصال التحويل لإتمام الطلب.',
        ];
    }
}
