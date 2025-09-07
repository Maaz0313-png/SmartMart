<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'formatted_price' => '$' . number_format($this->price, 2),
            'sku' => $this->sku,
            'stock_quantity' => $this->stock_quantity,
            'status' => $this->status,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'weight' => $this->weight,
            'dimensions' => $this->dimensions,
            'specifications' => $this->specifications,
            'tags' => $this->tags,
            'average_rating' => $this->reviews_avg_rating ? round($this->reviews_avg_rating, 1) : 0,
            'reviews_count' => $this->reviews_count ?? 0,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships
            'category' => new CategoryResource($this->whenLoaded('category')),
            'seller' => new UserResource($this->whenLoaded('user')),
            'images' => MediaResource::collection($this->whenLoaded('media')),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
        ];
    }
}