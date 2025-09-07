<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'price' => $this->price,
            'formatted_price' => '$' . number_format($this->price, 2),
            'stock_quantity' => $this->stock_quantity,
            'attributes' => $this->attributes,
            'is_active' => $this->is_active,
        ];
    }
}