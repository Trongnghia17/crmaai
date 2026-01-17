<?php

namespace App\Http\Requests\Order;

use App\Models\Order;
use App\Rules\NotExistsCondition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DetailRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
               Rule::exists('orders', 'id')
            ],
        ];
    }

    public function validationData()
    {
        return array_merge($this->all(), $this->route()->parameters);
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'id.required' => 'ID đơn hàng là bắt buộc',
            'id.integer' => 'ID đơn hàng không đúng định dạng',
            'id.exists' => 'ID đơn hàng không tồn tại',
        ];
    }
}
