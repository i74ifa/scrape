<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Primary gallery image (falls back to the first), exposed as a flat url
        // for list cards. Requires the `images` relation loaded.
        $primaryImage = null;
        $gallery = [];
        if ($this->relationLoaded('images')) {
            $primary = $this->images->firstWhere('is_primary', true) ?? $this->images->first();
            $primaryImage = $primary?->url;
            $gallery = $this->images->map(fn ($img) => [
                'url' => $img->url,
                'is_primary' => (bool) $img->is_primary,
            ])->values();
        }

        $range = $this->relationLoaded('variants') ? $this->priceRange() : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'price' => money($this->price),
            'sale_price' => $this->sale_price !== null ? money($this->sale_price) : null,
            'effective_price' => money($this->effectivePrice()),
            'price_range' => $range ? [
                'min' => money($range['min']),
                'max' => money($range['max']),
            ] : null,
            'end_discount_date' => $this->end_discount_date,
            'weight' => $this->weight,
            'sku' => $this->sku,
            'promotion' => $this->promotion,
            'tags' => $this->tags,
            'specifications' => $this->specifications ?? [],
            'has_variants' => (bool) $this->has_variants,
            'is_digital' => (bool) $this->is_digital,
            'is_active' => (bool) $this->is_active,
            'image' => $primaryImage,
            'images' => $gallery,
            'brand' => $this->whenLoaded('brand', fn () => $this->brand
                ? new CatalogBrandResource($this->brand)
                : null),
            'categories' => CatalogCategoryResource::collection($this->whenLoaded('categories')),
            'variants' => CatalogProductVariantResource::collection($this->whenLoaded('variants')),
        ];
    }
}
