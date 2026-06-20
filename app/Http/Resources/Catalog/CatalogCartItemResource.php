<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogCartItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $product = $this->product;
        $image = null;
        if ($product && $product->relationLoaded('images')) {
            $primary = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
            $image = $primary?->url;
        }

        return [
            'id' => $this->id,
            'product_id' => $this->catalog_product_id,
            'variant_id' => $this->product_variant_id,
            'name' => $product?->name,
            'variant_label' => $this->variantLabel(),
            'image' => $image,
            'quantity' => $this->quantity,
            'unit_price' => money($this->unitPrice()),
            'line_total' => money($this->lineTotal()),
            'available' => (bool) ($product?->is_active),
        ];
    }
}
