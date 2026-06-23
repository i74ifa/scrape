<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogOrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'subtotal' => money($this->subtotal),
            'total' => money($this->total),
            'total_quantity' => $this->total_quantity,
            'note' => $this->note,
            'payment_method' => $this->when($this->payment_method, fn () => $this->payment_method->value),
            'payment_method_label' => $this->when($this->payment_method, fn () => $this->payment_method->label()),
            'payment_reference' => $this->payment_reference,
            // Shipping address snapshot (survives address edits/deletes).
            'address' => $this->address_raw,
            'items' => CatalogOrderItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->whenCounted('items'),
            'created_at' => $this->created_at,
        ];
    }
}
