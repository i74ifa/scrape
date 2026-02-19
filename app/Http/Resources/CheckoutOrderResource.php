<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckoutOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'sub_total' => $this->sub_total,
            'grand_total' => $this->grand_total,
            'local_shipping' => $this->local_shipping,
            'shipping' => $this->shipping,
            'tax' => $this->tax,
            'discount' => $this->discount,
            'status' => $this->status->label(),
            'orders' => OrderResource::collection($this->whenLoaded('orders')),
            'address' => AddressResource::make($this->whenLoaded('address')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
