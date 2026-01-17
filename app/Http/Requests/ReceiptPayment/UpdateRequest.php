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

class UpdateRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                'exists:receipt_payment,id',
            ],
            'partner_group_id' => ['nullable', Rule::in([
                1,2,3,4,5
            ])],
            'partner_id' => [
                'nullable',
                'integer',
            ],
            'order_id' => [
                'nullable',
                'exists:orders,id',
            ],
            'price' => ['nullable'],
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
                'nullable',
                'exists:receipt_type,id',
            ],
            'partner_name' => [
                'nullable'
            ],
        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'Phiếu thu không tồn tại',
            'partner_group_id.required' => 'Nhóm đối tượng không được bỏ trống',
            'partner_id.required' => 'Tên đối tượng không được bỏ trống',
            'partner_id.exists' => 'Tên đối tượng không tồn tại',
            'receipt_type_id.required' => 'Loại phiếu không được bỏ trống',
            'receipt_type_id.exists' => 'Loại phiếu không tồn tại',
            'payment_type.in' => 'Loại thanh toán không đúng',
        ];
    }

    public function validationData()
    {
        return array_merge($this->all(), $this->route()->parameters);
    }
}
