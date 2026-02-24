<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'grand_total' => $this->grand_total,
            'status' => trans($this->status->value),

            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'platform' => $this->whenLoaded('platform'),
        ];
    }
}
