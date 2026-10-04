<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllocationPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'month' => $this->month,
            'income_id' => $this->income_id,
            'template_code' => $this->template_code,
            'items' => $this->items->map(function ($item) {
                return [
                    'bucket' => $item->bucket,
                    'percent' => $item->percent,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
