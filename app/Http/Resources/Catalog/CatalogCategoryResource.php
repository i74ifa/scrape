<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogCategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'image' => $this->imageUrl(),
            'parent_id' => $this->parent_id,
            'depth' => (int) $this->depth,
            'products_count' => $this->whenCounted('products'),
            'children' => CatalogCategoryResource::collection($this->whenLoaded('children')),
        ];
    }
}
