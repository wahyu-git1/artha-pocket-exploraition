<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'type' => $this->type,
            'bucket' => $this->bucket,
            'icon' => $this->icon,
            'color' => $this->color,
            'is_default' => $this->is_default,
        ];
    }
}
