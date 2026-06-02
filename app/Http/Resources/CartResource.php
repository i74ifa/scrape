<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
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
            'user_id' => $this->user_id,
            'platform_id' => $this->platform_id,
            'subtotal' => money($this->subtotal),
            'tax' => money($this->tax),
            'shipping' => money($this->shipping),
            'total' => money($this->total),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'items' => $this->whenLoaded('items', fn() => CartItemResource::collection($this->items)),
            'platform' => $this->whenLoaded('platform', fn() => new PlatformResource($this->platform)),
        ];
    }
}
