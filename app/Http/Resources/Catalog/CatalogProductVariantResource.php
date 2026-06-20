<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogProductVariantResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'price' => money($this->price),
            'sale_price' => $this->sale_price !== null ? money($this->sale_price) : null,
            'effective_price' => money($this->effectivePrice()),
            'weight' => $this->weight,
            'is_active' => (bool) $this->is_active,
            // The option values this variant is built from, with their swatches.
            'options' => $this->whenLoaded('attributeValues', fn () => $this->attributeValues->map(fn ($v) => [
                'attribute' => $v->relationLoaded('attribute') && $v->attribute ? $v->attribute->name : null,
                'value' => $v->value,
                'color' => $v->color,
            ])->values()),
        ];
    }
}
