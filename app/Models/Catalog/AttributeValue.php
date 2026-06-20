<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A store-wide choice on an axis ("أحمر" on "اللون"). Shared across products;
 * the swatch (color) is authored here once.
 */
class AttributeValue extends Model
{
    protected $fillable = ['attribute_id', 'value', 'position', 'color'];

    protected $casts = [
        'position' => 'integer',
    ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'attribute_value_variant');
    }
}
