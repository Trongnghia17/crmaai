<?php

namespace App\Http\Requests\CustomerDebt;

use Illuminate\Foundation\Http\FormRequest;

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
            'id' => ['required', 'integer'],
            'note' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric'],
        ];
    }

    public function messages()
    {
        return [
            'price.numeric' => 'Số tiền phải là số',
        ];
    }
}
