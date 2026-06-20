<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogCartResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'items' => CatalogCartItemResource::collection($this->items),
            'total_quantity' => $this->totalQuantity(),
            'subtotal' => money($this->subtotal()),
            'total' => money($this->subtotal()),
        ];
    }
}
