<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'total_money' => ['nullable', 'numeric'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_person_phone' => ['nullable', 'string', 'max:255'],
            'surrogate' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            "name.required" => "Tên nhà cung cấp không được để trống",
            "name.string" => "Tên nhà cung cấp phải là chuỗi",
            "name.max" => "Tên nhà cung cấp không được vượt quá 255 ký tự",
            "email.email" => "Email không đúng định dạng",
            "phone.string" => "Số điện thoại phải là định dạng chuỗi",
            "address.max" => "Địa chỉ không quá 255 ký tự",
            "total_money.numeric" => "Tài khoản nhà cung cấp phải là dạng số!",
            "contact_person.max" => "Tên người liên hệ không quá 255 ký tự",
            "contact_person_phone.max" => "Số điện thoại người liên hệ không quá 255 ký tự",
            "surrogate.max" => "Người đại diện không quá 255 ký tự",
            "note.max" => "Ghi chú không quá 255 ký tự",
        ];
    }
}
