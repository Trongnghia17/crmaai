<?php

namespace App\Http\Requests\Order;

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
            "phone" => ['nullable', 'max:14'],
            "email" => ['nullable', 'email'],
            "customer_name" => ['nullable'],
            "vat" => ['nullable', 'numeric'],
            "discount" => ['nullable', 'numeric'],
            "status" => ['required', Rule::in([1, 2, 3, 4, 5, 6])],
            "order_detail" => ['required', 'array'],
            "order_detail.*.product_id" => [
                'nullable',
                'integer',
                'id' => [
                    'required',
                    Rule::exists('products', 'id')
                ],
            ],
            "order_detail.*.quantity" => ['required'],
            "order_detail.*.price" => ['required'],
        ];

    }
    public function messages(): array
    {
        return [
            'phone.max' => 'Số điện thoại không được vượt quá 14 ký tự',
            'email.email' => 'Email không đúng định dạng',
            'vat.numeric' => 'VAT phải là dạng số thập phân!',
            'discount.numeric' => 'Chiết khấu phải là dạng số thập phân!',
            'status' => 'Trạng thái đơn hàng không tồn tại!',
            'order_detail.required' => 'Sản phẩm bán ra không được bỏ trống',
            'order_detail.*.product_id.required' => 'Sản phẩm không tồn tại',
            'order_detail.*.quantity.required' => 'Vui lòng nhập số lượng sản phẩm',
            'order_detail.*.price.required' => 'Giá tiền của sản phẩm không được bỏ trống',
        ];
    }
}
