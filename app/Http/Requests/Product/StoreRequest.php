<?php

namespace App\Http\Requests\Product;

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
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'category_ids' => ['nullable', 'array'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'is_buy_always' => ['nullable', 'boolean'],
            'sku' => ['nullable', 'string', 'max:255'],
            'base_cost' => ['required', 'numeric'],
            'retail_cost' => ['required', 'numeric'],
            'wholesale_cost' => ['required', 'numeric'],
            'in_stock' => ['required', 'numeric'],
            'sold' => ['required', 'numeric'],
            'temporality' => ['required', 'numeric'],
            'available' => ['required', 'numeric'],
            'unit' => ['nullable', 'string'],
            'barcode' => ['nullable', 'string', 'max:255', Rule::unique('products', 'barcode')->where(function ($query) {
                $query->where('user_id', auth()->id());
            })],
            'is_show' => ['nullable', 'boolean'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên sản phẩm không được để trống',
            'name.string' => 'Tên sản phẩm phải là chuỗi',
            'name.max' => 'Tên sản phẩm không được vượt quá 255 ký tự',
            'image.image' => 'Ảnh sản phẩm phải là ảnh',
            'description.string' => 'Mô tả sản phẩm phải là chuỗi',
            'is_active.boolean' => 'Trạng thái sản phẩm phải là boolean',
            'is_buy_always.boolean' => 'Trạng thái mua sản phẩm phải là boolean',
            'sku.string' => 'SKU sản phẩm phải là chuỗi',
            'sku.max' => 'SKU sản phẩm không được vượt quá 255 ký tự',
            'base_cost.required' => 'Giá gốc sản phẩm không được để trống',
            'base_cost.numeric' => 'Giá gốc sản phẩm phải là số',
            'retail_cost.required' => 'Giá bán lẻ sản phẩm không được để trống',
            'retail_cost.numeric' => 'Giá bán lẻ sản phẩm phải là số',
            'wholesale_cost.required' => 'Giá bán sỉ sản phẩm không được để trống',
            'wholesale_cost.numeric' => 'Giá bán sỉ sản phẩm phải là số',
            'in_stock.required' => 'Số lượng tồn kho sản phẩm không được để trống',
            'in_stock.numeric' => 'Số lượng tồn kho sản phẩm phải là số',
            'sold.required' => 'Số lượng đã bán sản phẩm không được để trống',
            'sold.numeric' => 'Số lượng đã bán sản phẩm phải là số',
            'temporality.required' => 'Số lượng tạm thời sản phẩm không được để trống',
            'temporality.numeric' => 'Số lượng tạm thời sản phẩm phải là số',
            'available.required' => 'Số lượng có sẵn sản phẩm không được để trống',
            'available.numeric' => 'Số lượng có sẵn sản phẩm phải là số',
            'barcode.numeric' => 'Mã vạch sản phẩm phải là số',
            'barcode.unique' => 'Mã vạch sản phẩm đã tồn tại',
        ];

    }
}
