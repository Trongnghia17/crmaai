<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmOTPRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'gmail' => ['required', 'email'],
            'otp' => ['required', 'integer'],
        ];
    }

    public function messages()
    {
        return [
            'gmail.required' => 'Email là bắt buộc',
            'gmail.email' => 'Email không hợp lệ',
            'otp.required' => 'OTP là bắt buộc',
            'otp.integer' => 'OTP phải là số',
        ];
    }
}
