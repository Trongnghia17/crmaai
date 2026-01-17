<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
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
            "name" => ['required', 'string', 'max:20'],
            "email" => ['required', 'string', 'email'],
            "address" => ['nullable', 'string'],
            "phone" => ['required', 'string', 'max:14'],
            "password" => ['required', 'string', 'min:8'],

        ];

    }
    public function messages(): array
    {
        return [
            'name.required' => 'Tên không được bỏ trống',
            'email.email' => 'Email không đúng định dạng',
            'phone.numeric' => 'phone phải là dạng số thập phân!',
            'phone.required' => 'Số điện thoại không được bỏ trống!',
            'phone.max' => 'Số điện thoại không được vượt quá 14 ký tự',
            'password.required' => 'Mật khẩu không được bỏ trống',
        ];
    }
}
