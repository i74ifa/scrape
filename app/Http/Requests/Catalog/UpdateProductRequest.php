<?php

namespace App\Http\Requests\Catalog;

use App\Rules\UploadedProductImage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
        $product = $this->route('product');

        return [
            'name' => ['required', 'string', 'max:255'],
            'name_translations' => ['nullable', 'array'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'description_translations' => ['nullable', 'array'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lte:price'],
            'end_discount_date' => ['nullable', 'date'],
            'weight' => ['nullable', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:30'],
            'slug' => [
                'nullable', 'string', 'max:255',
                Rule::unique('catalog_products', 'slug')->ignore($product->id),
            ],
            'promotion' => ['nullable', 'string', 'max:255'],
            'specifications' => ['nullable', 'array'],
            'tags' => ['nullable', 'string'],
            'brand_id' => ['nullable', Rule::exists('brands', 'id')],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => [Rule::exists('categories', 'id')],
            'has_variants' => ['boolean'],
            'is_digital' => ['boolean'],
            'is_active' => ['boolean'],

            // Gallery: paths already uploaded via the image upload endpoint.
            // Omit the key to leave the gallery untouched; send [] to clear it.
            'images' => ['nullable', 'array'],
            'images.*.path' => ['required', 'string', new UploadedProductImage],
            'images.*.is_primary' => ['boolean'],

            // has_variants flow: when supplied, the option catalog + variants are
            // re-synced. Variants are matched by SKU so existing rows are preserved.
            'options' => ['nullable', 'array'],
            'options.*.name' => ['required_with:options', 'string', 'max:255'],
            'options.*.is_color' => ['boolean'],
            'options.*.values' => ['required_with:options', 'array', 'min:1'],
            'options.*.values.*.value' => ['required', 'string', 'max:255'],
            'options.*.values.*.color' => ['nullable', 'string', 'max:30'],

            'variants' => ['nullable', 'array'],
            'variants.*.option_values' => ['required_with:variants', 'array', 'min:1'],
            'variants.*.option_values.*' => ['string'],
            'variants.*.sku' => ['nullable', 'string', 'max:30'],
            'variants.*.barcode' => ['nullable', 'string', 'max:100'],
            'variants.*.price' => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.cost_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.weight' => ['nullable', 'string', 'max:255'],
            'variants.*.is_active' => ['boolean'],
        ];
    }
}
