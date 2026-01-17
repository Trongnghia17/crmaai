<?php

namespace App\Http\Requests\Inventory;

use App\Models\Inventory;
use App\Models\Product;
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
            'id' => ['required',
                Rule::exists('inventory', 'id')],
        ];
    }


    public function messages()
    {
        return [
            'id.required' => 'Phiếu kiểm kho không được bỏ trống',
            'id.exists' => 'Phiếu kiểm kho không tồn tại',
        ];
    }
}
