<?php

namespace App\Http\Resources;

use App\Enums\OrderStatus;
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
            'status_history' => OrderStatus::getTimelines($this->status_history, $this->platform),

            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'platform' => $this->whenLoaded('platform'),
        ];
    }
}
