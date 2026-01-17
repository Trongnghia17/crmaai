<?php

namespace App\Http\Requests\Inventory;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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

            "status" => ['required', 'boolean'],
            "note" => ['nullable'],
            "inventories_detail" => [
                'required',
                'array'
            ],
            "inventories_detail.*.product_id" => [
                'required',
                'integer',
                Rule::exists('products', 'id')
            ],
            "inventories_detail.*.quantity_current" => ['required'],
            "inventories_detail.*.quantity_reality" => ['required'],
            "inventories_detail.*.note" => ['nullable'],
        ];

    }

    public function messages()
    {
        return [
            'inventories_detail.required' => 'Nhập đầy đủ thông tin chi tiết',
            'inventories_detail.array' => 'Thông tin chi tiết không đúng định dạng',
            'inventories_detail.*.product_id.required' => 'Mã sản phẩm không được bỏ trống',
            'inventories_detail.*.product_id.integer' => 'Mã sản phẩm không đúng định dạng',
            'inventories_detail.*.product_id.exists' => 'Mã sản phẩm không tồn tại trên hệ thống',
            'inventories_detail.*.quantity_current.exists' => 'Tồn kho không được bỏ trống',
            'inventories_detail.*.quantity_reality.required' => 'Tồn kho thực tế không được bỏ trống',
        ];
    }
}
