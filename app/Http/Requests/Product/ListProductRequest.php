<?php

namespace App\Http\Requests\Product;

use App\Rules\NoSpecialCharacters;
use Illuminate\Foundation\Http\FormRequest;

class ListProductRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'nullable',
            ],
            'per_page' => [
                'nullable',
                'integer',
            ],
            'page' => [
                'nullable',
                'integer',
            ],
            'type' => [
                'nullable',
                'integer',
            ],
        ];
    }

    public function validationData()
    {
        return array_merge($this->all(), $this->route()->parameters);
    }

}
