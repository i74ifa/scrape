<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Store-wide attribute (axis of choice): "اللون", "التخزين". Defined once;
 * products reference its values through variants rather than copying them.
 */
class Attribute extends Model
{
    protected $fillable = ['name', 'position', 'is_color'];

    protected $casts = [
        'position' => 'integer',
        'is_color' => 'boolean',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->orderBy('position');
    }

    /**
     * Categories that offer this attribute.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_attribute');
    }

    /**
     * Merge an attribute (name + value rows) into the library and return a map of
     * value-string → attribute_value id for variant linking. Idempotent and
     * accumulate-only: an existing attribute keeps its values, new ones are
     * appended, and a value's swatch (color) is refreshed when provided.
     *
     * @param  array<int, array{value: string, color?: string|null}>  $values
     * @param  array<int, int>  $categoryIds
     * @return array<string, int> value string → attribute_value id
     */
    public static function remember(string $name, array $values, array $categoryIds = [], ?bool $isColor = null): array
    {
        $name = trim($name);
        if ($name === '') {
            return [];
        }

        $attribute = static::firstOrCreate(['name' => $name]);

        if ($isColor !== null && $attribute->is_color !== $isColor) {
            $attribute->update(['is_color' => $isColor]);
        }

        if (! empty($categoryIds)) {
            $attribute->categories()->syncWithoutDetaching($categoryIds);
        }

        $map = [];
        foreach (array_values($values) as $i => $row) {
            $value = trim($row['value'] ?? '');
            if ($value === '') {
                continue;
            }

            $attributeValue = $attribute->values()->firstOrNew(['value' => $value]);
            if (! $attributeValue->exists) {
                $attributeValue->position = $i;
            }
            if (array_key_exists('color', $row) && $row['color'] !== null && $row['color'] !== '') {
                $attributeValue->color = $row['color'];
            }
            $attributeValue->save();

            $map[$value] = $attributeValue->id;
        }

        return $map;
    }
}
