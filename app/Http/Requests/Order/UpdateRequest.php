<?php

namespace App\Http\Requests\Order;

use App\Models\OrderDetail;
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
            'id' => ['required', 'integer', Rule::exists('orders', 'id')],
            "phone" => ['nullable', 'max:14'],
            "email" => ['nullable', 'email'],
            "customer_id" => ['nullable', 'integer', Rule::exists('customers', 'id')],
            "name" => ['nullable'],
            "address" => ['nullable'],
            "discount" => ['nullable'],
            "payment_type" => ['nullable'],
            "vat" => ['nullable'],
            "discount_type" => ['nullable', 'integer'],
            "is_retail" => ['nullable', 'boolean'],
            "create_date" => ['nullable'],
            "supplier_id" => ['nullable', 'integer'],
            "order_detail" => ['nullable', 'array'],
            "status" => ['nullable', 'integer', Rule::in([1, 2, 3, 4])],
            "order_detail.*.product_id" => ['nullable', 'integer', Rule::exists('products', 'id')],
            "order_detail.*.quantity" => ['nullable'],
            "order_detail.*.id" => ['nullable', 'integer', Rule::exists('order_detail', 'id')],
            "order_detail.*.price" => ['required'],
            "order_detail.*.is_delete" => ['nullable', 'boolean'],
            "user_id" => ['nullable', 'integer'],
        ];
    }

    public function validationData()
    {
        return array_merge($this->all(), $this->route()->parameters);
    }


    public function messages()
    {
        return [
            'phone.max' => 'SĐT không hợp lệ',
            'id.required' => 'Mã đơn hàng không được bỏ trống',
            'id.integer' => 'Mã đơn hàng không đúng định dạng',
            'point.integer' => 'Điểm không đúng định dạng',
            'id.exists' => 'Mã đơn hàng không tồn tại',
            'status.integer' => 'Trạng thái đơn hàng không đúng định dạng',
            'status.in' => 'Trạng thái đơn hàng không tồn tại',
            'order_detail.*.id.integer' => 'Mã chi tiết sản phẩm không đúng định dạng',
            'order_detail.*.id.exists' => 'Mã chi tiết sản phẩm không tồn tại',
            'order_detail.*.product_id.integer' => 'Mã sản phẩm không đúng định dạng',
            'order_detail.*.product_id.exists' => 'Mã sản phẩm không tồn tại',
            'order_detail.*.is_delete.boolean' => 'is_delete không đúng định dạng',
            'order_detail.*.price.required' => 'Giá bán không bỏ trống',
        ];
    }
}
