<?php

namespace App\Http\Requests\ReceiptPayment;

use App\Models\Customer;
use App\Models\Order;
use App\Models\ReceiptPayment;
use App\Models\ReceiptType;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'partner_group_id' => ['required', Rule::in([
                1,2,3,4,5
            ])],
            'partner_id' => [
                'nullable',
                'integer',
            ],
            'partner_name' => [
                'nullable'
            ],
            'order_id' => ['nullable', 'exists:orders,id'],
            'price' => ['required', 'gt:0'],
            'payment_type' => ['nullable', Rule::in([
                ReceiptPayment::BANK,
                ReceiptPayment::CASH,
                ReceiptPayment::COD,
                ReceiptPayment::CREDITS,
            ])],
            'is_other_income' => ['nullable', 'boolean'],
            'note' => ['nullable'],
            'time' => ['nullable', 'date_format:Y-m-d'],
            'receipt_type_id' => [
                'required',
                'exists:receipt_type,id',
            ],
        ];
    }

    public function messages()
    {
        return [
            'partner_group_id.required' => 'Nhóm đối tượng không được bỏ trống',
            'partner_id.required' => 'Tên đối tượng không được bỏ trống',
            'partner_id.exists' => 'Tên đối tượng không tồn tại',
            'receipt_type_id.required' => 'Loại phiếu không được bỏ trống',
            'receipt_type_id.exists' => 'Loại phiếu không tồn tại',
            'price.gt' => 'Giá trị phải lơn hơn 0',
            'payment_type.in' => 'Loại thanh toán không đúng',
        ];
    }
}
