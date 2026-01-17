<?php

namespace App\Http\Requests\Employee;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {

        return Employee::where('user_id', $this->route('id'))->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "name" => ['nullable', 'string', 'max:20'],
            "email" => ['nullable', 'string', 'email'],
            "address" => ['nullable', 'string'],
            "phone" => ['nullable', 'string', 'max:14'],
            "password" => ['nullable', 'string', 'min:8'],

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
