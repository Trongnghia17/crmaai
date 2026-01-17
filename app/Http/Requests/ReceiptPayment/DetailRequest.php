<?php

namespace App\Http\Requests\ReceiptPayment;

use Illuminate\Foundation\Http\FormRequest;

class DetailRequest extends FormRequest
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

    public function messages(): array
    {
        return [
            'id.required' => 'Phiếu thu không tồn tại',
        ];
    }

    public function validationData(): ?array
    {
        return array_merge($this->all(), $this->route()->parameters);
    }
}
