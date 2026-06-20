<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * A catalog-product gallery image path must be one the upload endpoint produced:
 * it lives directly under products/images/ on the configured disk and the file
 * exists. This blocks path traversal and references to arbitrary files — the
 * product save only ever receives paths, never raw files.
 */
class UploadedProductImage implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! Str::startsWith($value, 'products/images/')) {
            $fail('الصورة غير صالحة.');

            return;
        }

        if (Str::contains($value, '..')) {
            $fail('الصورة غير صالحة.');

            return;
        }

        if (! Storage::disk(config('filesystems.default'))->exists($value)) {
            $fail('الصورة غير موجودة.');
        }
    }
}
