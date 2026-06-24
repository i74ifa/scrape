<?php

namespace App\Http\Requests\AppSection;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppSectionRequest extends FormRequest
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
            'page' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9\-]+$/'],
            'name' => [
                'required',
                Rule::in(['BannerSwipe', 'BannerGrid', 'CustomBanner', 'ProductSwipe', 'ProductGrid']),
            ],
            'content' => ['required', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'page.required' => 'يرجى تحديد الصفحة',
            'page.regex' => 'معرّف الصفحة يجب أن يحتوي على أحرف إنجليزية صغيرة وأرقام وشرطات فقط',
            'name.required' => 'يرجى اختيار نوع القسم',
            'name.in' => 'نوع القسم غير صالح',
            'content.required' => 'محتوى القسم مطلوب',
        ];
    }
}
