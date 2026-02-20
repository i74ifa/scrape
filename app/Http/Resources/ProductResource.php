<?php

namespace App\Http\Resources;

use App\Services\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'name' => $this->name,
            'price' => Currency::format($this->price, 'SAR'),
            'image' => $this->image,
            'weight' => $this->weight,
            'description' => $this->description,
            'url' => $this->url,
            'platform' => $this->whenLoaded('platform', function () {
                return new PlatformResource($this->platform);
            }),
            'created_at' => $this->when($this->created_at, $this->created_at),
            'updated_at' => $this->when($this->updated_at, $this->updated_at),
        ];
    }
}
