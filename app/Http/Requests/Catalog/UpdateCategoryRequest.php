<?php

namespace App\Http\Requests\Catalog;

use App\Models\Catalog\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
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
        $category = $this->route('category');

        return [
            'name' => ['required', 'string', 'max:255'],
            'name_translations' => ['nullable', 'array'],
            'parent_id' => [
                'nullable',
                Rule::notIn([$category->id]), // a category cannot be its own parent
                Rule::exists('categories', 'id'),
                // Reject a descendant as parent (would create a cycle). Walk up
                // from the chosen parent; depth-bounded, so a few cheap lookups.
                function (string $attribute, $value, $fail) use ($category): void {
                    $cursor = $value;
                    $hops = 0;
                    while ($cursor && $hops++ < 50) {
                        if ((int) $cursor === $category->id) {
                            $fail('لا يمكن اختيار تصنيف فرعي تابع لهذا التصنيف كأب.');

                            return;
                        }
                        $cursor = Category::whereKey($cursor)->value('parent_id');
                    }
                },
            ],
            'slug' => [
                'nullable', 'string', 'max:255',
                Rule::unique('categories', 'slug')->ignore($category->id),
            ],
            'image' => ['nullable', 'image', 'max:4096'],
            'remove_image' => ['boolean'],
        ];
    }
}
