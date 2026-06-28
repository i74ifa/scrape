<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendFcmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'url' => ['nullable', 'string', 'max:1000'],
            'mutable_content' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'يرجى إدخال عنوان الإشعار',
            'body.required' => 'يرجى إدخال نص الإشعار',
            'image.image' => 'الملف يجب أن يكون صورة',
            'image.mimes' => 'صيغة الصورة غير مدعومة (مسموح: jpeg, png, webp)',
            'image.max' => 'حجم الصورة يجب ألا يتجاوز 4 ميجابايت',
        ];
    }
}
