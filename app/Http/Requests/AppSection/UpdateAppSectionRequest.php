<?php

namespace App\Http\Requests\AppSection;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Page is fixed once a section is created — only its type and content change.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                Rule::in(['BannerSwipe', 'BannerGrid', 'CustomBanner', 'ProductSwipe', 'ProductGrid']),
            ],
            'content' => ['required', 'array'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'يرجى اختيار نوع القسم',
            'name.in' => 'نوع القسم غير صالح',
            'content.required' => 'محتوى القسم مطلوب',
        ];
    }
}
