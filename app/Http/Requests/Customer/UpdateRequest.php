<?php

namespace App\Http\Requests\Customer;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
            'id' => ['required',
                Rule::exists('customers', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255', Rule::unique('customers', 'phone')->ignore($this->id)],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'total_money' => ['nullable', 'numeric'],
            'note' => ['nullable', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên khách hàng không được để trống',
            'name.string' => 'Tên phải là chuỗi',
            'name.max' => 'Tên không được vượt quá 255 ký tự',
            'phone.required' => 'Số điện thoại không được để trống',
            'phone.string' => 'Số điện thoại phải là chuỗi',
            'phone.max' => 'Số điện thoại không được vượt quá 255 ký tự',
            'phone.unique' => 'Số điện thoại này đã được sử dụng',
            'email.email' => 'Email không đúng định dạng',
            'email.max' => 'Email không được vượt quá 255 ký tự',
            'address.string' => 'Địa chỉ phải là chuỗi',
            'address.max' => 'Địa chỉ không được vượt quá 255 ký tự',
            'total_money.numeric' => 'Tổng tiền phải là số',
        ];
    }
}
