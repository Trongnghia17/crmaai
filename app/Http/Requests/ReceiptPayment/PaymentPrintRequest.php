<?php

namespace App\Http\Requests\ReceiptPayment;

use App\Models\ReceiptPayment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use PhpParser\Node\Expr\Cast\Double;

class PaymentPrintRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                'exists:receipt_payment,id',
            ],
        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'Phiếu thu không tồn tại',
            'id.integer' => 'Phiếu thu không đúng định dạng',
            'id.exists' => 'Phiếu thu không tồn tại',
        ];
    }

    public function validationData()
    {
        return array_merge($this->all(), $this->route()->parameters);
    }
}
