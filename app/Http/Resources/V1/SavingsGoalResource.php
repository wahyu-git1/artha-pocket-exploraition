<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SavingsGoalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'price' => $this->price,
            'saved_amount' => $this->saved_amount,
            'target_date' => $this->target_date,
            'monthly_amount' => $this->monthly_amount,
            'status' => $this->status,
            'completed_at' => $this->completed_at,
            'progress_percent' => $this->progress_percent,
            'months_left' => $this->months_left,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
