<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('attributes', 'name'),
            ],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_color' => ['boolean'],

            'values' => ['required', 'array', 'min:1'],
            'values.*.value' => ['required', 'string', 'max:255', 'distinct'],
            'values.*.color' => ['nullable', 'string', 'max:30'],

            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => [Rule::exists('categories', 'id')],
        ];
    }
}
