<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Casts\Attribute as CastAttribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * One image in a catalog product's gallery. The file is a path string on the
 * configured disk, exposed to the frontend as a full `url`.
 */
class ProductImage extends Model
{
    protected $fillable = ['product_id', 'path', 'is_primary', 'position'];

    protected $casts = [
        'is_primary' => 'boolean',
        'position' => 'integer',
    ];

    protected $appends = ['url'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Public URL for the stored path.
     */
    protected function url(): CastAttribute
    {
        return CastAttribute::get(function () {
            if (! $this->path) {
                return null;
            }

            return Str::startsWith($this->path, ['http://', 'https://'])
                ? $this->path
                : Storage::url($this->path);
        });
    }
}
