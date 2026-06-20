<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogOrderItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Image is best-effort from the still-linked product (may be null if the
        // product was later deleted — the snapshot fields keep the line intact).
        $image = null;
        if ($this->relationLoaded('product') && $this->product && $this->product->relationLoaded('images')) {
            $primary = $this->product->images->firstWhere('is_primary', true) ?? $this->product->images->first();
            $image = $primary?->url;
        }

        return [
            'id' => $this->id,
            'product_id' => $this->catalog_product_id,
            'variant_id' => $this->product_variant_id,
            'name' => $this->name,
            'variant_label' => $this->variant_label,
            'image' => $image,
            'unit_price' => money($this->unit_price),
            'quantity' => $this->quantity,
            'total' => money($this->total),
        ];
    }
}
