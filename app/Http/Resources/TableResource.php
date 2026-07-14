<?php

namespace App\Http\Resources;

use App\Models\Table;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Table
 */
class TableResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'zone' => $this->zone,
            'capacity' => $this->capacity,
            'shape' => $this->shape,
            'pos_x' => $this->pos_x,
            'pos_y' => $this->pos_y,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
