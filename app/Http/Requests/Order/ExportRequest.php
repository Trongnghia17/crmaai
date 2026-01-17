<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportRequest extends FormRequest
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
            "from" => ['nullable', 'date'],
            "to" => ['nullable', 'date'],
            "type" => ['nullable', 'numeric'],
        ];

    }
    public function messages(): array
    {
        return [
            'from.date' => 'Ngày bắt đầu không đúng định dạng',
            'to.date' => 'Ngày kết thúc không đúng định dạng',
        ];
    }
}
