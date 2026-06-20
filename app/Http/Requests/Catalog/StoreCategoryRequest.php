<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'name_translations' => ['nullable', 'array'],
            'parent_id' => [
                'nullable',
                Rule::exists('categories', 'id'),
            ],
            'slug' => [
                'nullable', 'string', 'max:255',
                Rule::unique('categories', 'slug'),
            ],
            'image' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
