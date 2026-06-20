<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttributeRequest extends FormRequest
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
        $attributeId = $this->route('attribute')->id;

        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('attributes', 'name')->ignore($attributeId),
            ],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_color' => ['boolean'],

            'values' => ['required', 'array', 'min:1'],
            // `id` lets the controller match an existing value row (rename/recolor
            // in place) instead of dropping and recreating it — which would break
            // variants referencing it. Absent ids = new rows.
            'values.*.id' => ['nullable', 'integer'],
            'values.*.value' => ['required', 'string', 'max:255', 'distinct'],
            'values.*.color' => ['nullable', 'string', 'max:30'],

            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => [Rule::exists('categories', 'id')],
        ];
    }
}
